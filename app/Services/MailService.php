<?php


namespace App\Services;


use App\Mail\ContactMessageMail;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Log;
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
            Log::channel('error_file')->error(["Ошибка отправки письма Админу о новом пользователе"]);
            return false;
        }


    }

    /**
     * @return void
     */
    public function sendAddNewObj(): void
    {
        try {
            Mail::raw('Добавлен новый объект...', function ($message) {
                $message->to(config('app.admin_email'))
                    ->subject('Новый объект');
            });
        } catch (\Exception $e) {
            Log::channel('error_file')->error(["Ошибка отправки письма Админу о новом пользователе" => $e]);
            $this->adminAlertService->sendMsgToAdmin("Ошибка отправки почты", "Письмо не отправлено. Ошибка в логе");
        }

    }

    /**
     * @return void
     */
    public function sendAddNewSubj(): void
    {
        try {
            Mail::raw('Добавлен новый объект...', function ($message) {
                $message->to(config('app.admin_email'))
                    ->subject('Новый объект');
            });
        } catch (\Exception $e) {
            Log::channel('error_file')->error(["Ошибка отправки письма Админу о новом пользователе" => $e]);
            $this->adminAlertService->sendMsgToAdmin("Ошибка отправки почты", "Письмо не отправлено. Ошибка в логе");
        }
    }


    public function sendContactMessage(array $data): void
    {
        try {
            Mail::to($data['email'])
                ->send(new SendMail(
                    $data['subject'],
                    $data['message']
                ));
        } catch (\Exception $e) {
            Log::channel('error_file')->error([
                'error' => 'Ошибка отправки письма через Mailable',
                'email' => $data['email'],
                'exception' => $e->getMessage(),
            ]);

            $this->adminAlertService->sendMsgToAdmin(
                "Ошибка отправки почты",
                "Письмо не отправлено. Ошибка в логе."
            );
        }
    }
}