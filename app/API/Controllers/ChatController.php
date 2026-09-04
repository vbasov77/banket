<?php

namespace App\API\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController
{
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // 1. Находим ID всех собеседников и время последнего сообщения
            $chatsQuery = DB::table('messages')
                ->where(function ($query) use ($userId) {
                    $query->where('from_user_id', $userId)
                        ->orWhere('to_user_id', $userId);
                })
                ->select([
                    DB::raw("CASE WHEN from_user_id = {$userId} THEN to_user_id ELSE from_user_id END AS partner_id"),
                    DB::raw('MAX(created_at) AS last_message_at'),
                ])
                ->groupBy('partner_id');

            $chats = $chatsQuery->get();

            if ($chats->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            $partnerIds = $chats->pluck('partner_id')->toArray();

            // 2. Получаем непрочитанные сообщения для всех партнёров одним запросом
            $unreadCounts = DB::table('messages')
                ->whereIn('from_user_id', $partnerIds)
                ->where('to_user_id', $userId)
                ->where('status', 0)
                ->groupBy('from_user_id')
                ->get(['from_user_id as partner_id', DB::raw('COUNT(*) AS unread_count')]);

            $unreadMap = $unreadCounts->pluck('unread_count', 'partner_id')->all();

            // 3. Получаем данные пользователей (партнёров) одним запросом
            $partners = User::whereIn('id', $partnerIds)->get()->keyBy('id');

            // 4. Собираем итоговый результат
            $result = $chats->map(function ($chat) use ($userId, $unreadMap, $partners) {
                $partnerId = $chat->partner_id;
                $partner = $partners->get($partnerId);

                // Получаем последнее сообщение
                $lastMessage = DB::table('messages')
                    ->where(function ($q) use ($userId, $partnerId) {
                        $q->where([
                            ['from_user_id', '=', $userId],
                            ['to_user_id', '=', $partnerId],
                        ])->orWhere([
                            ['from_user_id', '=', $partnerId],
                            ['to_user_id', '=', $userId],
                        ]);
                    })
                    ->orderByDesc('created_at')
                    ->first();

                // Форматируем дату
                $lastTime = null;
                if ($lastMessage && $lastMessage->created_at) {
                    $carbon = $lastMessage->created_at instanceof \Carbon\Carbon
                        ? $lastMessage->created_at
                        : \Carbon\Carbon::parse($lastMessage->created_at);
                    $lastTime = $carbon->toIso8601String();
                }

                return [
                    'id' => $partnerId,
                    'title' => $partner ? $partner->name : 'Чат без имени',
                    'last_message' => $lastMessage ? $lastMessage->body : null,
                    'last_message_status' => $lastMessage ? $lastMessage->status : null,
                    'last_time' => $lastTime,
                    'unread_count' => $unreadMap[$partnerId] ?? 0,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('ChatController@index failed', [
                'user_id' => $request->user()?->id ?? 'unknown',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось загрузить список чатов. Попробуйте позже.',
            ], 500);
        }
    }

    public function hasNewMessages(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // Проверяем, есть ли хоть одно непрочитанное сообщение (status = 0)
            $hasNew = DB::table('messages')
                ->where('to_user_id', $userId)
                ->where('status', 0)
                ->exists();

            return response()->json([
                'success' => true,
                'has_new' => $hasNew,
            ]);

        } catch (\Exception $e) {
            Log::channel('error_file')->error('ChatController@hasNewMessages failed', [
                'user_id' => $request->user()?->id ?? 'unknown',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'has_new' => false,
                'message' => 'Не удалось проверить новые сообщения.',
            ], 500);
        }
    }

}