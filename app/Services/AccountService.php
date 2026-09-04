<?php

namespace App\Services;

use App\Mail\PasswordChangedMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AccountService extends Service
{
    /**
     * Меняет пароль, если текущий совпадает.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        Mail::to($user->email)->send(new PasswordChangedMail($user));

        return true;
    }
}
