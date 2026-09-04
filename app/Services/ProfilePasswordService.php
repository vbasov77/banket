<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Mail\PasswordChangedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProfilePasswordService
{
    public function changePassword(User $user, array $data): bool
    {
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['password'] ?? '';

        // Проверка текущего пароля
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        // Обновление пароля
        $user->password = Hash::make($newPassword);
        $user->save();

        try {
            Mail::to($user->email)->send(new PasswordChangedMail($user));
        } catch (\Exception $e) {
            Log::channel('error_file')->error(["Ошибка отправки письма о смене пароля" => $e]);
            $adminAlertService = new AdminAlertService();
            $adminAlertService->sendMsgToAdmin("Ошибка отправки почты", "Письмо не отправлено. 
            Ошибка отправки письма о смене пароля. Ошибка в логе");
        }


        return true;
    }
}
