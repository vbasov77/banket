<?php


namespace App\Repositories;


use App\API\Service\NotificationService;
use App\Models\Message;
use App\Models\User;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageRepository extends Repository
{
    protected NotificationService $notificationService;

    /**
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


    public function findMyMessages(): array
    {
        $userId = (int)Auth::id();

        if ($userId === 0) {
            return [];
        }

        // 1. Получаем ID последних сообщений для каждой пары
        $lastMessageIds = DB::table('messages')
            ->where(function ($query) use ($userId) {
                $query->where('from_user_id', $userId)
                    ->orWhere('to_user_id', $userId);
            })
            // Выносим CASE в selectRaw, чтобы корректно передать параметр
            ->selectRaw('MAX(id) AS id, CASE WHEN from_user_id = ? THEN to_user_id ELSE from_user_id END AS peer_id', [$userId])
            ->groupBy('peer_id')
            ->pluck('id');

        if ($lastMessageIds->isEmpty()) {
            return [];
        }

        // Подзапрос для непрочитанных (безопасный)
        $unreadSubquery = DB::table('messages')
            ->where('to_user_id', $userId)
            ->where('status', 0)
            ->groupBy('to_user_id', 'from_user_id')
            ->select(
                'to_user_id',
                'from_user_id',
                DB::raw('COUNT(*) AS unread_count')
            );

        return Message::query()
            ->join('users AS peer', function ($join) use ($userId) {
                $join->whereRaw(
                    '(messages.from_user_id = ? AND messages.to_user_id = peer.id) OR '
                    . '(messages.to_user_id = ? AND messages.from_user_id = peer.id)',
                    [$userId, $userId]
                );
            })
            ->leftJoinSub($unreadSubquery, 'unread', function ($join) {
                $join->on(
                    DB::raw('LEAST(messages.from_user_id, messages.to_user_id)'),
                    '=',
                    DB::raw('LEAST(unread.from_user_id, unread.to_user_id)')
                )
                    ->on(
                        DB::raw('GREATEST(messages.from_user_id, messages.to_user_id)'),
                        '=',
                        DB::raw('GREATEST(unread.from_user_id, unread.to_user_id)')
                    );
            })
            ->whereIn('messages.id', $lastMessageIds)
            ->select([
                'messages.id',
                'messages.body',
                'messages.status',
                'messages.created_at',
                'messages.from_user_id',
                'peer.id AS peer_id',
                'peer.name AS peer_name',
                DB::raw('COALESCE(unread.unread_count, 0) AS unread_count'),
            ])
            ->orderBy('messages.created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getChatMessages(int $userId, int $toUserId): array
    {
        // Защита от запроса чата с самим собой (опционально, но полезно)
        if ($userId === $toUserId) {
            return [];
        }

        return Message::query()
            ->where(function ($query) use ($userId, $toUserId) {
                // Берём только сообщения строго между этой парой пользователей
                $query->where('from_user_id', $userId)->where('to_user_id', $toUserId)
                    ->orWhere('from_user_id', $toUserId)->where('to_user_id', $userId);
            })
            ->with([
                'fromUser' => fn($q) => $q->select('id', 'name'),
                'toUser' => fn($q) => $q->select('id', 'name'),
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($userId) {
                $isMine = $message->from_user_id === $userId;
                $otherUserId = $isMine ? $message->to_user_id : $message->from_user_id;

                $otherUser = $isMine ? $message->toUser : $message->fromUser;
                $otherName = $otherUser?->name ?? 'Собеседник';

                return [
                    'id' => $message->id,
                    'from_user_id' => $message->from_user_id,
                    'to_user_id' => $message->to_user_id,
                    'body' => $message->body,
                    'status' => $message->status,
                    'created_at' => $message->created_at->format('H:i, d.m.Y'),
                    'is_mine' => $isMine,
                    'other_user_id' => $otherUserId,
                    'other_name' => $otherName,
                ];
            })
            ->toArray();
    }

    public function markAsReadForUser(int $userId, array $messageIds): int
    {
        if (empty($messageIds)) {
            return 0;
        }

        return Message::whereIn('id', $messageIds)
            ->where('to_user_id', $userId)
            ->where('status', 0)
            ->update(['status' => 1]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function store(array $data): array
    {
        try {
            $this->validatePayload($data);

            $payload = [
                'from_user_id' => (int)$data['from_user_id'],
                'to_user_id' => (int)$data['to_user_id'],
                'body' => $this->sanitizeBody($data['body']),
                'status' => 0,
            ];

            $id = DB::table('messages')->insertGetId($payload);

            if (!$id) {
                throw new \Exception('Не удалось получить ID после вставки сообщения.');
            }

            // Получаем created_at
            $rawDate = DB::table('messages')
                ->where('id', $id)
                ->value('created_at');

            $createdAt = $rawDate
                ? Carbon::parse($rawDate)->format('d.m.Y, H:i')
                : null;

            if ($createdAt === null) {
                throw new \Exception('Не удалось получить created_at для сообщения ID: ' . $id);
            }

            // --- ВОТ ЗДЕСЬ ДОБАВЛЯЕМ ПУШ ---
            $fcmToken = User::where('id', (int)$payload['to_user_id'])->value('fcm_token');
            if ($fcmToken) {
                $firebase = new FirebaseService();
                $response = $firebase->sendPushWithCode(
                    $fcmToken,
                    'Новое сообщение',
                    ['from_user_id' => (string)$payload['from_user_id'],
                        'code' => 111
                    ]
                );
            }


            return [
                'bool' => true,
                'id' => $id,
                'body' => $payload['body'],
                'date' => $createdAt,
                'from_user_id' => $payload['from_user_id'],
                'to_user_id' => $payload['to_user_id'],
            ];
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('MessageRepository::store failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input_data' => array_map(function ($v) {
                    return is_string($v) ? substr($v, 0, 100) : $v;
                }, $data),
            ]);

            return [
                'bool' => false,
                'error' => 'Не удалось сохранить сообщение. Попробуйте позже.',
            ];
        }
    }

    private function validatePayload(array $data): void
    {
        if (!isset($data['from_user_id']) || !is_numeric($data['from_user_id'])) {
            throw new \InvalidArgumentException('from_user_id обязателен и должен быть числом.');
        }
        if (!isset($data['to_user_id']) || !is_numeric($data['to_user_id'])) {
            throw new \InvalidArgumentException('to_user_id обязателен и должен быть числом.');
        }
        if (!isset($data['body']) || !is_string($data['body'])) {
            throw new \InvalidArgumentException('body обязателен и должен быть строкой.');
        }
        if (trim($data['body']) === '') {
            throw new \InvalidArgumentException('Сообщение не может быть пустым.');
        }
    }

    private function sanitizeBody(string $body): string
    {
        return strip_tags($body);
    }
}