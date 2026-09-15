<?php

namespace App\Exceptions;

use App\Services\AdminAlertService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // 1. Валидация — 422
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'message' => 'Ошибка валидации данных',
                    'errors' => $e->errors(),
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid()
                ], 422);
            }
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        });

        // 2. Аутентификация — 401
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'unauthorized',
                    'message' => 'Требуется авторизация',
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid()
                ], 401);
            }
            return redirect()->guest(route('login'));
        });

        // 3. Не найдено — 404
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            $path = $request->path();

            // Мусорные пути от ботов/сканеров — тихо отдаём 404 без логирования
            $junk = [
                'wp-admin', 'wp-login', 'wp-content', 'xmlrpc.php',
                '.env', '.git', 'send.php', 'mail.php', 'test.php',
                'phpinfo', 'admin.php', 'config.php',
            ];

            foreach ($junk as $item) {
                if (str_contains($path, $item)) {
                    return $request->expectsJson()
                        ? response()->json(['error' => 'not_found'], 404)
                        : response()->view('errors.404', [], 404);
                }
            }

            // Обычные 404 — отдаём страницу
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Ресурс не найден',
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid()
                ], 404);
            }
            return response()->view('errors.500', [], 404);
        });

        // 4. Бизнес‑исключения — используем их статус
        $this->renderable(function (CitySearchException $e, Request $request) {
            return response()->json([
                'error' => 'city_search_error',
                'message' => $e->getMessage(),
                'correlation_id' => (string) \Illuminate\Support\Str::uuid()
            ], $e->getStatusCode());
        });

        // 5. Неверный HTTP-метод — 405
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'method_not_allowed',
                    'message' => 'Неверный HTTP-метод для этого маршрута',
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid()
                ], 405);
            }
            return redirect()->back()->with('error', 'Не удалось выполнить действие: неверный метод запроса.');
        });

        // 6. Все остальные исключения — 500
        $this->renderable(function (Throwable $exception, Request $request) {
            $correlationId = (string) \Illuminate\Support\Str::uuid();

            Log::channel('error_file')->error('Unhandled exception', [
                'exception_type' => $exception::class,
                'message' => $exception->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String(),
                'correlation_id' => $correlationId,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'internal_server_error',
                    'message' => 'Внутренняя ошибка сервера. Пожалуйста, обратитесь к администратору.',
                    'correlation_id' => $correlationId
                ], 500);
            }

            $adminAlertService = new AdminAlertService();
            $adminAlertService->sendMsgToAdmin("Ошибка 500", "Ошибка 500 в логе");

//            return response()->view('errors.500', [], 500);
        });
    }
}
