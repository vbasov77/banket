<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $words = [
            'яблоко', 'ромашка', 'кошка', 'чайник',
            'радуга', 'облако', 'дорога', 'солнце',
            'ветер', 'поле', 'лес', 'мост', 'окно', 'книга'
        ];

        $word = $words[array_rand($words)];

        session()->put('register_captcha_word', mb_strtolower($word));
        session()->put('register_captcha_expires', now()->addMinutes(5));

        return view('auth.register', compact('word'));
    }

    public function store(Request $request): RedirectResponse
    {
        $captchaWord = session('register_captcha_word');
        $expiresAt = session('register_captcha_expires');

        if (!$captchaWord || now()->greaterThan($expiresAt)) {
            return back()
                ->withErrors(['captcha' => __('Captcha session expired. Please refresh the page.')])
                ->withInput();
        }

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'captcha'       => ['required'],
        ]);

        $userInput = mb_strtolower(trim($request->input('captcha')));

        if ($userInput !== $captchaWord) {
            return back()
                ->withErrors(['captcha' => __('Incorrect word. Please check spelling.')])
                ->withInput();
        }

        session()->forget('register_captcha_word');
        session()->forget('register_captcha_expires');

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        $message = "Регистрация прошла успешно. На ваш email {$request->email} была выслана ссылка на подтверждение.\n"
            . "Пройдите по ней, чтобы подтвердить почту. Если письма не пришло, проверьте папку «Спам».\n"
            . "Если вы неправильно указали email, удалите аккаунт в разделе 'Профиль'!";

        return redirect()->route('role.select')->with('message', $message);
    }
}
