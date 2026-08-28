<?php

namespace App\Http\Controllers;


use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    private MessageService $messageService;

    /**
     * @param MessageService $messageService
     */
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function show(Request $request, MessageService $service)
    {
        $userId = Auth::id();
        $toUserId = (int)$request->to_user_id;

        // Запрет на переписку с самим собой
        if ($userId === $toUserId) {
            return redirect()->back()->with('error', 'Нельзя писать самому себе');
            // Если у тебя AJAX/API, замени на:
            // return response()->json(['error' => 'Нельзя писать самому себе'], 403);
        }

        // 1. Получаем сообщения чата
        $messages = $service->chat($userId, $toUserId);
        $name = User::where('id', $toUserId)->value('name');

        // Теперь собеседник — это всегда тот, кого явно передали в запросе.
        $toUser = $toUserId;

        // Отмечаем непрочитанные сообщения
        $arrayIds = $this->messageService->getUnreadMessageIdsForChat($userId, $toUserId);
        if (count($arrayIds) > 0) {
            $this->messageService->changeStatus($arrayIds);
        }

        return view('messages.show', [
            'messages' => $messages,
            'userId' => $userId,
            'toUserId' => $toUserId, // передаём явно, чтобы в Blade брать именно его
            'toUser' => $toUser,   // можно оставить для совместимости, но теперь это просто копия $toUserId
            'name' => $name,
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

        $result = $this->messageService->store($validated);

        return response()->json($result);
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

        try {
            $result = $this->messageService->deleteMessages($ids);

            return response()->json([
                'answer' => 'ok',
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


    public function deleteChat(Request $request)
    {
        $userId = Auth::id();

        if (!$request->filled('from_user_id') || !$request->filled('to_user_id')) {
            return response()->json(['success' => false, 'message' => 'Недостаточно данных для удаления чата'], 400);
        }

        $fromUserId = (int)$request->from_user_id;
        $toUserId = (int)$request->to_user_id;

        // Проверка прав: пользователь должен быть участником чата
        if ($userId !== $fromUserId && $userId !== $toUserId) {
            Log::channel('error_file')->error('Попытка удаления чужого чата', [
                'user_id' => $userId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
            ]);
            return response()->json(['success' => false, 'message' => 'Вы не можете удалять чужой чат'], 403);
        }

        try {
            $deletedCount = Message::where(function ($query) use ($fromUserId, $toUserId) {
                $query->where('from_user_id', $fromUserId)
                    ->where('to_user_id', $toUserId);
            })
                ->orWhere(function ($query) use ($fromUserId, $toUserId) {
                    $query->where('from_user_id', $toUserId)
                        ->where('to_user_id', $fromUserId);
                })
                ->delete();


            $message = "Чат был удалён";

            return redirect()->route('messages', ['message' => $message]);

        } catch (\Exception $e) {
            Log::channel('error_file')->error('Ошибка при удалении чата', [
                'user_id' => $userId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Произошла ошибка при удалении чата'], 500);
        }
    }

    public function checkNewMsg(Request $request)
    {
        $messages = Message::where('to_user_id', $request->from_user_id)
            ->where('from_user_id', $request->to_user_id)
            ->where('obj_id', $request->obj_id)->where('status', 0)->get();
        if (!empty(count($messages))) {
            $array = [];
            $countMess = count($messages);
            for ($i = 0; $i < $countMess; $i++) {
                Message::where('id', $messages[$i]->id)->update(['status' => 1]);
                $array[] = $messages[$i];
            }
            $result = ['bool' => true, 'messages' => $array];
            exit(json_encode($result));
        }
        return response()->json([
            'bool' => false,
        ]);

    }

}
