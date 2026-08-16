<?php

namespace App\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        // 1. Валидация входных параметров
        $validator = Validator::make($request->all(), [
            'partner_id' => ['required', 'integer', 'min:1'],
            'last_timestamp' => ['nullable', 'numeric', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректные параметры запроса',
                'errors' => $validator->errors(),
            ], 400);
        }

        $partnerId = (int)$request->partner_id;
        $limit = $request->has('limit') ? (int)$request->limit : 50;
        $lastTimestamp = $request->has('last_timestamp') ? (float)$request->last_timestamp : 0;

        // 2. Проверка существования собеседника
        if (!\App\Models\User::find($partnerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Собеседник не найден',
            ], 404);
        }

        $authId = auth()->id();

        // Если пользователь запрашивает сообщения с самим собой — это странно, можно отклонить
        if ($authId === $partnerId) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя запрашивать сообщения с самим собой',
            ], 400);
        }

        // 3. Выборка сообщений между двумя пользователями
        $messages = Message::where(function ($query) use ($authId, $partnerId) {
            $query->where('from_user_id', $authId)->where('to_user_id', $partnerId)
                ->orWhere('from_user_id', $partnerId)->where('to_user_id', $authId);
        })
            ->when($lastTimestamp > 0, function ($query) use ($lastTimestamp) {
                // Конвертируем UNIX timestamp в формат даты для сравнения с created_at
                $date = \Carbon\Carbon::createFromTimestamp($lastTimestamp);
                $query->where('created_at', '>', $date);
            })
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        // 4. Подготовка ответа
        $data = $messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'from_user_id' => $msg->from_user_id,
                'to_user_id' => $msg->to_user_id,
                'body' => $msg->body,
                'status' => $msg->status,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        });

        // next_cursor — временная метка последнего сообщения (для подгрузки истории)
        $nextCursor = $messages->isNotEmpty()
            ? $messages->last()->created_at->timestamp
            : null;

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'has_more' => $messages->count() === $limit,
                'next_cursor' => $nextCursor,
                'total_count' => $messages->count(),
            ],
        ], 200);
    }
}