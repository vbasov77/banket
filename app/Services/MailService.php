<?php


namespace App\Services;



use Illuminate\Support\Facades\Mail;

class MailService extends Service
{
    protected AdminAlertService $adminAlertService;

    /**
     * @param AdminAlertService $adminAlertService
     */
    public function __construct(AdminAlertService $adminAlertService)
    {
        $this->adminAlertService = $adminAlertService;
    }

    /**
     * @return bool
     */
    public function sendUserRegister(): bool
    {
        try {
            Mail::raw('Добавлен новый пользователь...', function ($message) {
                $message->to(config('app.admin_email'))
                    ->subject('Новый пользователь');
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }


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