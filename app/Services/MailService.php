<?php


namespace App\Services;


use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;

class MailService extends Service
{
    /**
     * @return void
     */
    public function sendUserRegister(): void
    {

        Mail::raw('Добавлен новый пользователь...', function ($message) {
            $message->to(config('app.admin_email'))
                ->subject('Новый пользователь');
        });
    }

    /**
     * @return void
     */
    public function sendAddNewObj(): void
    {
        Mail::raw('Добавлен новый объект...', function ($message) {
            $message->to(config('app.admin_email'))
                ->subject('Новый объект');
        });
    }

    /**
     * @return void
     */
    public function sendAddNewSubj(): void
    {
        Mail::raw('Добавлен новый объект...', function ($message) {
            $message->to(config('app.admin_email'))
                ->subject('Новый объект');
        });
    }


    public function sendContactMessage(array $data): void
    {
        Mail::raw($data['message'], function ($mess) use ($data) {
            $mess->to($data['email'])->subject($data['subject']);
        });
    }
}