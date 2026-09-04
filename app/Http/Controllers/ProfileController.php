<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\EmailChangeVerificationMail;
use App\Services\AdminAlertService;
use App\Services\ImgBanSubjService;
use App\Services\ProfilePasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected ImgBanSubjService $imgBanSubjService;

    /**
     * @param ImgBanSubjService $imgBanSubjService
     */
    public function __construct(ImgBanSubjService $imgBanSubjService)
    {
        $this->imgBanSubjService = $imgBanSubjService;
    }


    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update()
    {
        /**
         *
         */
    }

    /**
     * @return View
     */
    public function show(Request $request): View
    {
        $user = Auth::user();

        if (!$user) {
            abort(404, 'Пользователь не найден');
        }

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'is_verified' => !is_null($user->email_verified_at),
        ];
        $message = $request->message ?? null;

        return view('profile.show', ['userData' => $userData, 'message' => $message]);
    }


    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Пользователь не найден. Пожалуйста, войдите снова.']);
        }

        // Проверка пароля
        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => 'Неверный пароль. Операция удаления отменена.'])
                ->withInput();
        }


        try {
            DB::beginTransaction();

            $this->imgBanSubjService->destroyWithProfile($user->id);
            // Прямое SQL‑удаление без вызова модели
            $deleted = DB::table('users')->where('id', $user->id)->delete();

            if (!$deleted) {
                throw new \Exception('Не удалось удалить пользователя из базы данных');
            }
            DB::commit();
            Auth::logout();

            return redirect()->route('front', ['message' => 'Ваш профиль успешно удалён.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error_file')->error('Ошибка удаления профиля (user_id: ' . $user->id . '): ' . $e->getMessage());

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении профиля. Попробуйте позже.'])
                ->withInput();
        }
    }

    public function deleteProfile()
    {
        return \view('auth.destroy_profile');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'password_confirmation' => ['same:password'],
        ]);

        $user = Auth::user();
        if (!$user) {
            abort(403, 'Доступ запрещён');
        }

        $passwordService = new ProfilePasswordService();
        if ($passwordService->changePassword($user, $request->all())) {
            // Важно: разлогиниваем пользователя, чтобы он залогинился заново с новым паролем
            Auth::logout();
            return redirect()->route('login')->with('password_changed', 'Пароль успешно изменён. Пожалуйста, войдите снова.');
        }

        return back()->withErrors(['current_password' => 'Неверный текущий пароль.']);
    }
}
