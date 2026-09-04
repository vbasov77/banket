<?php

namespace App\API\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    protected MessageService $messageService;

    /**
     * @param MessageService $messageService
     */
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'partner_id' => ['required', 'integer', 'min:1'],
            'last_timestamp' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректные параметры запроса',
                'errors' => $validator->errors()->toArray(),
            ], 400);
        }

        $partnerId = (int)$request->partner_id;
        $lastTimestamp = $request->has('last_timestamp') ? (float)$request->last_timestamp : 0;

        $authId = auth()->id();

        // Отмечаем непрочитанные сообщения
        $arrayIds = $this->messageService->getUnreadMessageIdsForChat($authId, $partnerId);
        if (count($arrayIds) > 0) {
            $this->messageService->changeStatus($arrayIds);
            $fcmToken = User::where('id', (int)$partnerId)->value('fcm_token');
            if ($fcmToken) {
                $firebase = new FirebaseService();
                $response = $firebase->sendPushWithCode(
                    $fcmToken,
                    'Сообщение прочитано',
                    ['from_user_id' => (string)$authId,
                        'code' => 222,
                        'message_ids' => implode( ',', $arrayIds)
                    ]
                );
            }
        }

        if (!$authId) {
            return response()->json(['success' => false, 'message' => 'Пользователь не авторизован'], 401);
        }

        if ($authId === $partnerId) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя запрашивать сообщения с самим собой',
            ], 400);
        }

        $partner = User::find($partnerId);
        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'Собеседник не найден',
            ], 404);
        }

        // Основной запрос: двусторонняя переписка
        $query = Message::with(['fromUser:id,name', 'toUser:id,name'])
            ->where(function ($q) use ($authId, $partnerId) {
                $q->where('from_user_id', $authId)->where('to_user_id', $partnerId)
                    ->orWhere('from_user_id', $partnerId)->where('to_user_id', $authId);
            });

        // Фильтр по времени только если передан корректный last_timestamp
        if ($lastTimestamp > 0) {
            $date = \Carbon\Carbon::createFromTimestamp($lastTimestamp);
            $query->where('created_at', '>', $date);
        }

        // Лимит для пагинации: можно вынести в конфиг или константу
        $limit = 20;
        $messages = $query->orderBy('created_at', 'desc')->limit($limit + 1)->get();

        // Проверяем, есть ли «ещё»
        $hasMore = $messages->count() > $limit;
        if ($hasMore) {
            $messages = $messages->take($limit);
        }

        $data = $messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'from_user_id' => $msg->from_user_id,
                'to_user_id' => $msg->to_user_id,
                'body' => $msg->body,
                'status' => $msg->status,
                'created_at' => $msg->created_at->timestamp,
                'from_name' => $msg->fromUser?->name,
                'to_name' => $msg->toUser?->name,
            ];
        });

        $nextCursor = null;
        if ($hasMore || $messages->isNotEmpty()) {
            // Если мы обрезали список, берём timestamp последнего из «обрезанного» набора
            $nextCursor = $messages->last()->created_at->timestamp;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'has_more' => $hasMore,
                'next_cursor' => $nextCursor,
            ],
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_user_id' => ['required', 'integer', 'min:1'],
            'body' => ['required', 'string', 'max:4000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректные данные',
                'errors' => $validator->errors(),
            ], 400);
        }

        $authId = auth()->id();
        $toId = (int)$request->to_user_id;
        $body = $request->body;

        // Нельзя писать самому себе
        if ($authId === $toId) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя отправлять сообщения самому себе',
            ], 400);
        }

        // Проверяем, существует ли собеседник
        if (!User::find($toId)) {
            return response()->json([
                'success' => false,
                'message' => 'Собеседник не найден',
            ], 404);
        }

        // Сохраняем сообщение
        $message = Message::create([
            'from_user_id' => $authId,
            'to_user_id' => $toId,
            'body' => $body,
            'status' => 0, // например, 0 = отправлено, 1 = доставлено, 2 = прочитано
        ]);

        // --- ВОТ ЗДЕСЬ ДОБАВЛЯЕМ ПУШ ---
        $fcmToken = User::where('id', (int)$toId)->value('fcm_token');
        if ($fcmToken) {
            $firebase = new FirebaseService();
            $response = $firebase->sendPushWithCode(
                $fcmToken,
                'Новое сообщение',
                ['from_user_id' => (string)$authId,
                    'code' => 111
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $message->id,
                'from_user_id' => $message->from_user_id,
                'to_user_id' => $message->to_user_id,
                'body' => $message->body,
                'status' => $message->status,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        // Редактировать может только отправитель
        if ($message->from_user_id != $request->user()->id) {
            return response()->json(['error' => 'Недостаточно прав'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $message->update([
            'body' => $validated['body'],
        ]);

        return response()->json(['success' => true]);
    }


    public function deleteMsgApi(Request $request)
    {
        $ids = $request->input('ids');

        // Приводим к массиву и оставляем только числа
        $ids = array_filter((array)$ids, 'is_numeric');

        if (empty($ids)) {
            return response()->json([
                'answer' => 'error',
                'message' => 'Не переданы корректные ID сообщений',
            ], 400);
        }

        try {
            $deletedCount = Message::whereIn('id', $ids)->delete();

            return response()->json([
                'answer' => 'ok',
                'deleted_count' => $deletedCount,
                'ids' => array_values($ids), // опционально: вернуть, какие именно удалили
            ], 200);

        } catch (\Exception $e) {
            // Логируем реальную ошибку (в production лучше не отдавать её клиенту)
            Log::channel('error_file')->error('Ошибка удаления сообщений: ' . $e->getMessage(), [
                'ids' => $ids,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'answer' => 'error',
                'message' => 'Произошла ошибка при удалении сообщений',
            ], 500);
        }
    }


}