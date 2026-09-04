<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistrationMail;
use Illuminate\Support\Str;

class AdminUserRegistrationService
{
    public function registerByAdmin(string $name, string $email, ?User $admin = null): array
    {
        // Генерируем случайный пароль
        $password = Str::random(12);

        // Создаём пользователя
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            // если есть поле для «кто создал», можно сохранить $admin->id
        ]);

        event(new Registered($user));

        // Отправляем письмо
        try {
            Mail::to($user->email)->send(new UserRegistrationMail($user, $password));
        } catch (\Exception $e) {
            Log::channel('error_file')->error(["Ошибка отправки письма Админу о новом пользователе" => $e]);

            $adminAlertService = new AdminAlertService();
            $adminAlertService->sendMsgToAdmin("Ошибка отправки почты", "Письмо не отправлено. Ошибка в логе");

        }

        return [
            'success' => true,
            'user' => $user,
        ];
    }
}
