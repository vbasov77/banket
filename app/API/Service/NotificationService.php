<?php

namespace App\API\Service;

use App\Models\Message;
use App\Models\User;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\MessagingException;

class NotificationService extends Service
{

    public function sendPushNotificationToUser(int $userId, Message $message): void
    {
        $user = User::find($userId);

        if (!$user || empty($user->fcm_token)) {
            return;
        }

        $dataPayload = [
            'type' => 'new_message',
            'message_id' => (string)$message->id,
            'from_user_id' => (string)$message->from_user_id,
            'body' => $message->body,
        ];

        $messageObj = CloudMessage::fromArray([
            'token' => $user->fcm_token,
            'notification' => [
                'title' => 'Новое сообщение',
                'body' => 'Пришло новое сообщение в чате',
            ],
            'data' => $dataPayload,
        ]);

        try {
            Firebase::messaging()->send($messageObj);
        } catch (MessagingException $e) {
            // Firebase возвращает разные ошибки в одном исключении — смотрим текст/код
            $message = $e->getMessage();

            // Ключевые фразы, по которым понимаем, что токен «умер»
            if (str_contains($message, 'registration token is not registered')
                || str_contains($message, 'Unregistered')
                || str_contains($message, 'InvalidRegistration')) {

                User::where('id', $userId)->update(['fcm_token' => null]);
                return; // Дальше не логируем как общую ошибку
            }

            // Остальные ошибки — просто логируем
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error while sending FCM', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

}