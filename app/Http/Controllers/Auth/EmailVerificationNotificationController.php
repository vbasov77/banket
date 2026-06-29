<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Если email уже подтверждён — сразу выходим
        if ($user->hasVerifiedEmail()) {
            return back()->with('message', 'Ваш email уже подтверждён');
        }

        // 2. Проверяем, когда в последний раз отправляли ссылку (из сессии)
        if ($request->session()->has('verification_link_sent_at')) {
            $lastSent = Carbon::parse($request->session()->get('verification_link_sent_at'));

            // Если с момента последней отправки ещё не прошло 60 минут — блокируем повторную отправку
            if (!$lastSent->addMinutes(60)->isPast()) {
                return back()->with(
                    'message',
                    'Ссылка уже была отправлена. Повторный запрос будет доступен через некоторое время.'
                );
            }
        }

        // 3. Отправляем письмо (стандартный механизм Laravel)
        $user->sendEmailVerificationNotification();

        // 4. Записываем время отправки в сессию
        $request->session()->put('verification_link_sent_at', now());

        return back()->with('message', 'Ссылка для подтверждения email отправлена! Проверьте вашу почту.');
    }}
