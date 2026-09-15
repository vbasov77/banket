<?php

namespace App\Http\Controllers;


use App\Http\Requests\Chat\DeleteChatRequest;
use App\Models\Message;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    private MessageService $messageService;

    protected FirebaseService $firebaseService;

    public function __construct(MessageService  $messageService,
                                FirebaseService $firebaseService)
    {
        $this->messageService = $messageService;
        $this->firebaseService = $firebaseService;
    }

    public function show(Request $request, MessageService $service)
    {
        $userId    = Auth::id();
        $toUserId  = (int)$request->to_user_id;
        $page      = max(1, (int)$request->get('page', 1));
        $limit     = 20;

        if ($userId === $toUserId) {
            return redirect()->back()->with('error', 'Нельзя писать самому себе');
        }

        $paginator = $service->chat($userId, $toUserId, $limit, $page);

        $messages = $paginator->getCollection()
            ->reverse()
            ->map(function ($message) use ($userId) {
                $isMine = $message->from_user_id === $userId;
                return [
                    'id'           => $message->id,
                    'from_user_id' => $message->from_user_id,
                    'to_user_id'   => $message->to_user_id,
                    'body'         => $message->body,
                    'status'       => $message->status,
                    'created_at' => $message->created_at->format('Y-m-d\TH:i:s'),                    'is_mine'      => $isMine,
                ];
            })
            ->values()
            ->toArray();

        $name = User::where('id', $toUserId)->value('name');

        $arrayIds = $this->messageService->getUnreadMessageIdsForChat($userId, $toUserId);
        if (!empty($arrayIds)) {
            $this->messageService->changeStatus($arrayIds);

            $this->firebaseService->sendToUser($toUserId, 'Сообщение прочитано', [
                'from_user_id' => (string)$userId,
                'code'         => '222',
                'message_ids'  => implode(',', $arrayIds),
            ]);
        }

        return view('messages.show', [
            'messages'   => $messages,
            'pagination' => $paginator,
            'userId'     => $userId,
            'toUser'   => $toUserId,
            'name'       => $name,
        ]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_user_id' => 'required|integer',
            'to_user_id' => 'required|integer',
            'body' => 'required|string|max:4000',
        ]);
        $toUserId = $validated['to_user_id'];
        // Запрет писать самому себе
        if ($validated['from_user_id'] === $toUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя отправлять сообщения самому себе',
            ], 400);
        }

        // Проверка существования получателя (если в сервисе этого нет)
        if (!User::find($toUserId)) {
            return response()->json([
                'success' => false,
                'message' => 'Собеседник не найден',
            ], 404);
        }

        $result = $this->messageService->store($validated);

        // Если сохранение не удалось — сразу возвращаем ошибку из сервиса
        if (!$result['bool']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Не удалось сохранить сообщение. Попробуйте позже.',
            ], 500);
        }

        $preview = mb_substr($result['body'], 0, 50);

        // Пуш отправляем ТОЛЬКО после успешного сохранения
        $this->firebaseService->sendToUser(
            $toUserId,
            'Новое сообщение',
            [
                'from_user_id' => (string)$result['from_user_id'],
                'code' => '111',
                'body' => $preview,
                'message_id' => (string)$result['id'],
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $result['id'],
                'body' => $result['body'],
                'date' => $result['date'],
                'from_user_id' => $result['from_user_id'],
                'to_user_id' => $result['to_user_id'],
            ],
        ], 201);
    }

    public function notified(Request $request)
    {
        $notified = [];
        $count = count($request->array_id);
        for ($i = 0; $i < $count; $i++) {
            $not = Message::where('id', $request->array_id[$i])->first();
            if ($not->status == 1) {
                $notified[] = $not;
            }

        }
        exit(json_encode($notified));
    }

    public function myMessages(Request $request)
    {
        $myMessages = $this->messageService->findMyMessages();

        !empty($request->message) ? $message = $request->message : $message = null;

        return view('messages.my_messages', ['messages' => $myMessages, 'message' => $message]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMsg(Request $request): JsonResponse
    {
        $ids = $request->input('ids');
        $toUserId = $request->input('to_user_id');
        $userId = Auth::id();

        try {
            $result = $this->messageService->deleteMessages($ids);

            $this->firebaseService->sendToUser($toUserId, 'Сообщение удалено', [
                'from_user_id' => (string)$userId,
                'code' => '333',
                'message_ids' => implode(',', $ids),
            ]);

            return response()->json([
                'answer' => 'ok',
                'ids' => $ids,
                'deleted_count' => $result['deleted_count'],
            ], 200);
        } catch (\InvalidArgumentException $e) {
            // Плохой ввод — 400
            return response()->json(['answer' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            // Сбой БД/сервера — 500
            Log::channel('error_file')->error("Сбой БД/сервера — 500: " . $e->getMessage(), [
                'ids' => $ids,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['answer' => 'error', 'message' => 'Ошибка сервера'], 500);
        }
    }

    /**
     * @param DeleteChatRequest $request
     * @return JsonResponse
     */
    public function deleteChat(DeleteChatRequest $request)
    {
        $userId = Auth::id();
        $fromUserId = (int)$request->from_user_id;
        $toUserId = (int)$request->to_user_id;

        if ($userId !== $fromUserId && $userId !== $toUserId) {
            Log::channel('error_file')->error('Попытка удаления чужого чата', compact('userId', 'fromUserId', 'toUserId'));
            return response()->json(['success' => false, 'message' => 'Вы не можете удалять чужой чат'], 403);
        }

        try {
            Message::where(fn ($q) => $q->where('from_user_id', $fromUserId)->where('to_user_id', $toUserId))
                ->orWhere(fn ($q) => $q->where('from_user_id', $toUserId)->where('to_user_id', $fromUserId))
                ->delete();

            return response()->json(['success' => true, 'message' => 'Чат был удалён']);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(['Ошибка при удалении чата ' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Произошла ошибка при удалении чата'], 500);
    }
    }

    public function checkNewMsg(Request $request)
    {
        // 1. Валидация входящих данных
        $validated = $request->validate([
            'from_user_id' => 'required|integer|exists:users,id',
            'to_user_id' => 'required|integer|exists:users,id',
        ]);

        $fromUserId = $validated['from_user_id'];
        $toUserId = $validated['to_user_id'];

        // 2. Начинаем транзакцию (если упадет ошибка, ничего не сохранится)
        return DB::transaction(function () use ($fromUserId, $toUserId) {

            // 3. Получаем непрочитанные сообщения
            $query = Message::where('to_user_id', $fromUserId)
                ->where('from_user_id', $toUserId)
                ->where('status', 0);

            $messages = $query->get();

            // Если сообщений нет, возвращаем false
            if ($messages->isEmpty()) {
                return response()->json(['bool' => false]);
            }

            // 4. Массовое обновление статуса 0 <-> 1
            $arrayIds = $this->messageService->getUnreadMessageIdsForChat($fromUserId, $toUserId);
            if (count($arrayIds) > 0) {
                $this->messageService->changeStatus($arrayIds);

                $this->firebaseService->sendToUser($toUserId, 'Сообщение прочитано', [
                    'from_user_id' => (string)$fromUserId,
                    'code' => '222',
                    'message_ids' => implode(',', $arrayIds),
                ]);
            }


            // 5. Возвращаем данные
            return response()->json([
                'bool' => true,
                'messages' => $messages,
            ]);
        });
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id'           => 'required|integer',
            'body'         => 'required|string|max:5000',
            'to_user_id'   => 'required|integer',
            'from_user_id' => 'required|integer',
        ]);

        // Редактировать может только автор сообщения
        $message = Message::where('id', $validated['id'])
            ->where('from_user_id', auth()->id())
            ->first();

        if (!$message) {
            return response()->json(['answer' => 'error', 'message' => 'Сообщение не найдено'], 404);
        }

        $message->body = $validated['body'];
        $message->save();

        return response()->json(['answer' => 'ok']);
    }


    public function loadOlderMessages(Request $request, MessageService $service)
    {
        $userId = Auth::id();
        $toUserId = (int)$request->to_user_id;
        $page = max(1, (int)$request->page ?? 1);
        $limit = 20;

        if ($userId === $toUserId) {
            return response()->json(['messages' => []]);
        }

        // Используем тот же сервис, что и в show()
        $paginator = $service->chat($userId, $toUserId, $limit, $page);

        $messages = $paginator->getCollection()->map(function ($message) use ($userId) {
            $isMine = $message->from_user_id === $userId;
            return [
                'id'           => $message->id,
                'from_user_id' => $message->from_user_id,
                'to_user_id'   => $message->to_user_id,
                'body'         => $message->body,
                'status'       => $message->status,
                'created_at'   => $message->created_at->toISOString(), // удобно для JS
                'is_mine'      => $isMine,
            ];
        })->toArray();

        return response()->json([
            'messages'  => $messages,
            'has_more'  => $paginator->hasMorePages(),
            'next_page' => $paginator->currentPage() + 1,
        ]);
    }

}
