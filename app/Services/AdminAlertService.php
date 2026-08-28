<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;

class AdminAlertService extends Service
{
    private int $systemUserId = 2;   // системный аккаунт-отправитель
    private int $adminUserId = 1;    // админ (user1)

    public function sendMsgToAdmin(string $title, string $body): void
    {

        $admin = User::find($this->adminUserId);

        if (!$admin || !$admin->fcm_token) {
            Log::channel("error_file")->error('Admin or admin FCM token not found', ['admin_id' => $this->adminUserId]);
            return;
        }

        $message = Message::create([
            'from_user_id' => $this->systemUserId,
            'to_user_id' => $this->adminUserId,
            'body' => $body,
        ]);

        $fcmToken = $admin->fcm_token;
        if ($fcmToken) {
            $firebase = new FirebaseService();
            $response = $firebase->sendPush(
                $fcmToken,
                $title,
                $body,
                ['from_user_id' => $this->systemUserId]
            );
        }

    }
}
