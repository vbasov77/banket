<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public string $messageText;

    public function __construct(string $subject, string $messageText)
    {
        $this->subject = $subject;
        $this->messageText = $messageText;
    }

    public function build(): self
    {
        return $this->subject($this->subject)
            ->text('mails.plain', ['messageText' => $this->messageText]);
    }

}
