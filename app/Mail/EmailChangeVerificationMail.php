<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class EmailChangeVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $url;

    public function __construct(User $user)
    {
        $this->user = $user;
        // Стандартная Laravel-ссылка подтверждения, совместимая
        $this->url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
    }

    public function build()
    {
        return $this->subject('Подтвердите новый email')
            ->view('mails.email_change_verification');
    }
}
