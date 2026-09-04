<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\EmailChangedWarningMail;
use App\Mail\EmailChangeVerificationMail;
use App\Services\AccountService;
use App\Services\AdminAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;

class AccountController extends Controller
{
    protected AccountService $service;

    public function __construct(AccountService $service)
    {
        $this->service = $service;
    }

    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Запоминаем старый email ДО изменений
        $oldEmail = $user->email;

        $user->fill($request->validated());

        // Сменилась почта — новый адрес пока не подтверждён
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $user->fcm_token = null;
        }

        $user->save();

        // Если email реально изменился
        if ($user->wasChanged('email')) {
            // 1) Письмо на НОВЫЙ адрес — подтверждение
            try {
                Mail::to($user->email)->send(new EmailChangeVerificationMail($user));
            } catch (\Exception $e) {
                Log::channel('error_file')->error(["Ошибка отправки письма Админу о изменении почты" => $e]);
                $adminAlertService = new AdminAlertService();
                $adminAlertService->sendMsgToAdmin("Ошибка отправки почты", "Письмо не отправлено. Ошибка в логе");
            }

            // 2) Письмо на СТАРЫЙ адрес — предупреждение
            try {
                Mail::to($oldEmail)->send(new EmailChangedWarningMail($user, $oldEmail));
            } catch (\Exception $e) {
                Log::channel('error_file')->error(["Ошибка отправки письма на старый email при смене почты" => $e]);
            }
        }

        return Redirect::route('profile.show');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'password_confirmation' => ['same:password'],
        ]);

        $user = Auth::user();

        if (!$this->service->changePassword($user, $request->input('current_password'), $request->input('password'))) {
            return back()->withErrors(['current_password' => 'Текущий пароль указан неверно.']);
        }

        Auth::logout();

        return redirect()->route('login')->with('status', 'Пароль изменён. Войдите с новым паролем.');
    }
}