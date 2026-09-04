<?php

namespace App\Services;

use App\Models\DeviceToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private $messaging;

    public function __construct()
    {
        // Путь к ключу (на проде лучше брать из config/services.php, но для старта так ок)
        $serviceAccountPath = base_path('storage/app/firebase/service-account.json');

        if (!file_exists($serviceAccountPath)) {
            throw new \Exception("Service account file not found at: $serviceAccountPath");
        }

        $factory = (new Factory())->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Отправляет пуш-уведомление
     */
// app/Services/FirebaseService.php
    public function sendPush(string $token, string $title, string $body, array $data = []): array
    {
        try {
            $message = CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => $title,
                ],
                'data' => $data,
            ]);

            // Отправляем. Если тут ошибка — она уйдёт в catch
            $this->messaging->send($message);

            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            Log::channel('error_file')->error('FCM push failed', [
                'error_class'  => get_class($e),
                'error_message' => $e->getMessage(),
                'token_prefix'  => substr($token, 0, 10) . '...',
            ]);

            return [
                'success'      => false,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function sendPushWithCode(string $token,string $title, array $data): array
    {
        try {
            $message = CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => $title,
                ],
                'data' => $data,
            ]);
            // Отправляем. Если тут ошибка — она уйдёт в catch
            $this->messaging->send($message);

            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            Log::channel('error_file')->error('FCM push failed', [
                'error_class'  => get_class($e),
                'error_message' => $e->getMessage(),
                'token_prefix'  => substr($token, 0, 10) . '...',
            ]);

            return [
                'success'      => false,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function sendToUser(int $userId, string $title, array $data): void
    {
        $tokens = DeviceToken::where('user_id', $userId)
            ->where('is_active', true)
            ->where('type', 'fcm')
            ->pluck('token');

        foreach ($tokens as $fcmToken) {
            $this->sendPushWithCode($fcmToken, $title, $data);
        }
    }

}
