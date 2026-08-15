<?php

namespace App\API\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class MessageController
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'body'      => 'required|string|max:2000',
        ]);

        $fromUserId = auth()->id();
        $toUserId = (int)$request->input('to_user_id');

        $message = Message::create([
            'from_user_id' => $fromUserId,
            'to_user_id'   => $toUserId,
            'body'         => $request->input('body'),
            'status'       => 0,
        ]);

        // Пуш только если есть токен
        $receiver = \App\Models\User::find($toUserId);
        if ($receiver && $receiver->fcm_token) {
            try {
                $cloudMessage = CloudMessage::withTarget('token', $receiver->fcm_token)
                    ->withNotification([
                        'title' => 'Новое сообщение',
                        'body'  => 'У вас новое сообщение в чате',
                    ])
                    ->setData([
                        'message_id'    => (string)$message->id,
                        'from_user_id'  => (string)$fromUserId,
                        'to_user_id'    => (string)$toUserId,
                    ]);

                Firebase::messaging()->send($cloudMessage);
            } catch (\Exception $e) {
                Log::error('FCM send failed for user ' . $toUserId . ': ' . $e->getMessage());
            }
        }

        return response()->json($message, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
        ]);

        $currentUserId = auth()->id();
        $toUserId = (int)$request->input('to_user_id');
        $since = $request->input('since');

        $query = Message::where(function ($q) use ($currentUserId, $toUserId) {
            $q->where('from_user_id', $currentUserId)->where('to_user_id', $toUserId)
                ->orWhere('from_user_id', $toUserId)->where('to_user_id', $currentUserId);
        });

        if ($since) {
            $query->where('created_at', '>', \Carbon\Carbon::parse($since));
        }

        $messages = $query
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        return response()->json($messages);
    }
}
