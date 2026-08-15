<?php


namespace App\Repositories;


use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageRepository extends Repository
{

    public function findMyMessages(): array
    {
        $userId = Auth::id();

        // Шаг 1: получаем ID последних сообщений для каждой пары (я <-> собеседник)
        $lastMessageIds = DB::table('messages')
            ->where(function ($query) use ($userId) {
                $query->where('from_user_id', $userId)
                    ->orWhere('to_user_id', $userId);
            })
            // Безопасная подстановка через приведение к int (никаких ? в groupBy)
            ->groupBy(DB::raw('CASE WHEN from_user_id = ' . (int)$userId . ' THEN to_user_id ELSE from_user_id END'))
            ->selectRaw('MAX(id) AS id')
            ->pluck('id');

        if ($lastMessageIds->isEmpty()) {
            return []; // пустой массив вместо collect()
        }

        // Шаг 2: выбираем сообщения с отношениями и сразу превращаем в массив
        return Message::with(['fromUser', 'toUser'])
            ->whereIn('id', $lastMessageIds)
            ->get()
            ->toArray();
    }

    public function getChatMessages(int $userId, int $toUserId): array
    {
        return Message::query()
            ->whereIn('from_user_id', [$userId, $toUserId])
            ->whereIn('to_user_id', [$userId, $toUserId])
            // Убираем условие where('from_user_id', '!=', 'to_user_id'), если хочешь видеть и самосообщения (опционально)
            ->with(['fromUser' => fn($q) => $q->select('id', 'name'),
                'toUser'   => fn($q) => $q->select('id', 'name')])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) use ($userId) {
                $isMine = $message->from_user_id === $userId;
                $otherUserId = $isMine ? $message->to_user_id : $message->from_user_id;

                // Получаем имя собеседника: если это моё сообщение — берём toUser, иначе fromUser
                $otherUser = $isMine ? $message->toUser : $message->fromUser;
                $otherName = $otherUser?->name ?? 'Собеседник';

                return [
                    'id'           => $message->id,
                    'from_user_id' => $message->from_user_id,
                    'to_user_id'   => $message->to_user_id,
                    'body'         => $message->body,
                    'status'       => $message->status,
                    'created_at'   => $message->created_at->format('H:i, d.m.Y'),
                    'is_mine'      => $isMine,
                    'other_user_id'=> $otherUserId,
                    'other_name'   => $otherName,
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
                'to_user_id'   => (int)$data['to_user_id'],
                'body'         => $this->sanitizeBody($data['body']),
                'status'       => 0,
            ];

            $id = DB::table('messages')->insertGetId($payload);

            if (!$id) {
                throw new \Exception('Не удалось получить ID после вставки сообщения.');
            }

            $rawDate = DB::table('messages')
                ->where('id', $id)
                ->value('created_at');

            $createdAt = $rawDate
                ? Carbon::parse($rawDate)->format('d.m.Y, H:i')
                : null;

            if ($createdAt === null) {
                throw new \Exception('Не удалось получить created_at для сообщения ID: ' . $id);
            }

            return [
                'bool'         => true,
                'id'           => $id,
                'body'         => $payload['body'],
                'date'         => $createdAt,
                'from_user_id' => $payload['from_user_id'],
                'to_user_id'   => $payload['to_user_id'],
            ];
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('MessageRepository::store failed', [
                'message'      => $e->getMessage(),
                'code'         => $e->getCode(),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'input_data'   => array_map(function ($v) {
                    return is_string($v) ? substr($v, 0, 100) : $v;
                }, $data),
            ]);

            return [
                'bool'  => false,
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