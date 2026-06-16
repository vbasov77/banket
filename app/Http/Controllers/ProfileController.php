<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
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
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
            'is_verified' => !is_null($user->email_verified_at),
        ];
        $message = $request->message ?? null;

        return view('profile.show', ['userData' => $userData, 'message' => $message]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        // Валидация: поле password обязательно
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        // Проверка пароля
        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => 'Неверный пароль. Операция удаления отменена.'])
                ->withInput();
        }

        // Если пароль верный — удаляем пользователя
        $user->delete();

        // После удаления выходим из системы
        Auth::logout();

        return redirect()->route('front', ['message' => 'Ваш профиль успешно удалён.']);
    }

    public function deleteProfile()
    {
        return \view('auth.destroy_profile');
    }
}
