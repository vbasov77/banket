<?php

namespace App\Http\Controllers;


use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // 1. Получаем сообщения чата
        $messages = $service->chat($userId, $toUserId);
        $name = User::where('id', $toUserId)->value('name');
        // 2. Определяем ID собеседника
        $toUser = $toUserId;
        if (!empty($messages)) {
            $first = reset($messages);
            $toUser = ($first['from_user_id'] !== $userId)
                ? $first['from_user_id']
                : $first['to_user_id'];
        }

        // 3. Помечаем непрочитанные как прочитанные
        if (!empty($messages)) {
            $unreadIds = array_filter($messages, fn($m) => $m['to_user_id'] === $userId && $m['status'] == 0
            );
            $unreadIds = array_column($unreadIds, 'id');

            if (!empty($unreadIds)) {
                $service->markRead($userId, $unreadIds);

                // Обновляем статус локально в массиве, чтобы view сразу видел status = 1
                foreach ($messages as &$m) {
                    if (in_array($m['id'], $unreadIds)) {
                        $m['status'] = 1;
                    }
                }
            }
        }

        return view('messages.show', [
            'messages' => $messages,
            'userId' => $userId,
            'toUser' => $toUser,
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


    public function deleteMsg(Request $request)
    {
        Message::where('id', $request->id)->delete();
        $res = ['answer' => 'ok'];

        exit(json_encode($res));
    }

    public function deleteChat(Request $request)
    {
        Message::where('from_user_id', $request->from_user_id)
            ->orWhere('to_user_id', $request->to_user_id)
            ->orWhere('from_user_id', $request->to_user_id)
            ->orWhere('to_user_id', $request->from_user_id)
            ->delete();
        $message = "Чат был удалён";

        return redirect()->route('messages', ['message' => $message]);
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
