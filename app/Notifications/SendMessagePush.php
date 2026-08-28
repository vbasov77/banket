<?php

namespace App\Notifications;

use App\Models\Message;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendMessagePush extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Message $message,
        public int $recipientId
    ) {}

    public function via($notifiable)
    {
        return ['database', 'fcm']; // можно оставить только fcm, если не нужна отдельная таблица уведомлений
    }

    public function toFcm($notifiable)
    {
        // Получаем FCM token из таблицы user_fcm_tokens или профиля пользователя
        $token = $notifiable->fcm_token; // предполагаем, что у User есть поле fcm_token
        if (!$token) {
            return null;
        }

        $data = [
            'type' => 'message',
            'message_id' => $this->message->id,
            'from_user_id' => $this->message->from_user_id,
            'body' => $this->message->body,
            'chat_id' => $this->message->to_user_id, // удобно для открытия чата
        ];

        return CloudMessage::withBuilder(function (MessageBuilder $builder) use ($data) {
            $builder->withData($data);
            $builder->withNotification([
                'title' => 'Новое сообщение',
                'body' => "От пользователя {$this->message->from_user_id}: {$this->message->body}",
                // 'image' => 'URL картинки отправителя'
            ]);
            // Если хочешь, чтобы пуш будил приложение даже в Doze-режиме:
            $builder->withPriority('high');
        });
    }
}
