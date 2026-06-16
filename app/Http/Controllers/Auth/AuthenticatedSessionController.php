<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'errors' => session()->get('errors') ?? new \Illuminate\Support\MessageBag()
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CityService $cityService): RedirectResponse
    {
        $request->authenticate();

        // Получаем аутентифицированного пользователя
//        $user = Auth::user();

        // Проверяем, подтверждён ли email
//        if (!$user->hasVerifiedEmail()) {
//            // Разлогиниваем пользователя
//            Auth::logout();
//
//            // Небезопасная работа с сессией: проверяем инициализацию
//            if ($request->hasSession()) {
//                $session = $request->session();
//                if ($session->isStarted()) {
//                    $session->invalidate();
//                    $session->regenerateToken();
//                }
//            }
//
//            // Возвращаем с ошибкой
//            return redirect()->route('login')
//                ->withErrors([
//                    'email' => 'Пожалуйста, подтвердите ваш email, перейдя по ссылке из письма.'
//                ]);
//        }

        // Безопасная работа с сессией: проверяем инициализацию
        if ($request->hasSession()) {
            $session = $request->session();
            if ($session->isStarted()) {
                $session->regenerate();
            }
        }

        $cityService->findUserCity($request);

        return redirect()->route('my.obj');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Безопасная работа с сессией: проверяем, что сессия установлена и запущена
        if ($request->hasSession()) {
            $session = $request->session();
            if ($session->isStarted()) {
                $session->invalidate();
                $session->regenerateToken();
            }
        }

        return redirect('/');
    }
}
