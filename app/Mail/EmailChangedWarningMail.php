<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailChangedWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $oldEmail;

    public function __construct(User $user, string $oldEmail)
    {
        $this->user = $user;
        $this->oldEmail = $oldEmail;
    }

    public function build()
    {
        return $this->subject('Ваш email был изменён')
            ->view('mails.email_changed_warning');
    }
}
