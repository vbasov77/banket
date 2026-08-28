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
        Log::channel('info_file')->info([$user]);

        if (!$user || empty($user->fcm_token)) {
            Log::channel('info_file')->info(['FCM push skipped: no token for user_id=' . $userId]);
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
            $result = Firebase::messaging()->send($messageObj);

            Log::channel('info_file')->info('FCM send success', [
                'user_id' => $userId,
                'message_id' => $result->messageId(),
            ]);
        } catch (MessagingException $e) {
            // Firebase возвращает разные ошибки в одном исключении — смотрим текст/код
            $message = $e->getMessage();

            // Ключевые фразы, по которым понимаем, что токен «умер»
            if (str_contains($message, 'registration token is not registered')
                || str_contains($message, 'Unregistered')
                || str_contains($message, 'InvalidRegistration')) {

                Log::channel('info_file')->info(['Removing invalid FCM token for user ' . $userId]);
                User::where('id', $userId)->update(['fcm_token' => null]);
                return; // Дальше не логируем как общую ошибку
            }

            // Остальные ошибки — просто логируем
            Log::channel('info_file')->error('FCM request failed', [
                'user_id' => $userId,
                'error' => $message,
            ]);
        } catch (\Exception $e) {
            Log::channel('info_file')->error('Unexpected error while sending FCM', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

}