<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Важно: НЕ используем ShouldQueue, чтобы не требовать таблицу jobs
class PasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Пароль в системе изменён')
            ->view('mails.password_changed');
    }
}
