<?php

namespace App\Exceptions;

use App\Services\AdminAlertService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Мусорные пути от ботов/сканеров — 404/419 отдаём молча, в лог не пишем.
     */
    private function isJunkPath(Request $request): bool
    {
        $junk = [
            'wp-admin', 'wp-login', 'wp-content', 'xmlrpc.php',
            '.env', '.git', 'send.php', 'mail.php', 'test.php',
            'phpinfo', 'admin.php', 'config.php', 'rest_route',
        ];

        $path = $request->path();

        foreach ($junk as $item) {
            if (str_contains($path, $item)) {
                return true;
            }
        }

        return false;
    }

    public function register(): void
    {
        // 1. Валидация — 422
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'message' => 'Ошибка валидации данных',
                    'errors' => $e->errors(),
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
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
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
                ], 401);
            }
            return redirect()->guest(route('login'));
        });

        // 3. Не найдено — 404
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            // Мусорные пути от ботов/сканеров — тихо отдаём 404 без логирования
            if ($this->isJunkPath($request)) {
                return $request->expectsJson()
                    ? response()->json(['error' => 'not_found'], 404)
                    : response()->view('errors.404', [], 404);
            }

            // Обычные 404 — отдаём страницу
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Ресурс не найден',
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
                ], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        // 4. Бизнес-исключения — используем их статус
        $this->renderable(function (CitySearchException $e, Request $request) {
            return response()->json([
                'error' => 'city_search_error',
                'message' => $e->getMessage(),
                'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            ], $e->getStatusCode());
        });

        // 5. Неверный HTTP-метод — 405
        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'method_not_allowed',
                    'message' => 'Неверный HTTP-метод для этого маршрута',
                    'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
                ], 405);
            }
            return redirect()->back()->with('error', 'Не удалось выполнить действие: неверный метод запроса.');
        });

        // 5.1 CSRF — 419. Боты и истёкшие сессии — это шум, а не ошибка
        $this->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null; // остальные HttpException идут дальше по цепочке
            }

            // Сканеры WordPress и прочий мусор — даже в info не пишем
            if ($this->isJunkPath($request)) {
                return response()->view('errors.419', [], 419);
            }

            // Уровень info: история остаётся, мониторинг не тревожим
            Log::channel('error_file')->info('CSRF rejected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String(),
                'correlation_id' => (string) \Illuminate\Support\Str::uuid(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'csrf_mismatch',
                    'message' => 'Сессия истекла, обновите страницу',
                ], 419);
            }

            // Живому пользователю с протухшей сессией — назад с сообщением
            return redirect()->back()->with('error', 'Сессия истекла, попробуйте ещё раз');
        });

        // 5.2 Остальные HttpException (403, 429 throttle и т. д.).
        //     4xx — ожидаемые ответы, 5xx — реальные проблемы сервера
        $this->renderable(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            $correlationId = (string) \Illuminate\Support\Str::uuid();

            if ($status >= 500) {
                Log::channel('error_file')->error('HTTP server error', [
                    'status' => $status,
                    'message' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => $request->user()?->id,
                    'ip' => $request->ip(),
                    'timestamp' => now()->toIso8601String(),
                    'correlation_id' => $correlationId,
                ]);

                if (!$request->expectsJson()) {
                    (new AdminAlertService())->sendMsgToAdmin("Ошибка {$status}", "HTTP {$status} в логе");
                }
            } else {
                // 4xx — не ошибка, а ожидаемый ответ (403 запрещено, 429 лимит и т. п.)
                Log::channel('error_file')->info('HTTP exception', [
                    'status' => $status,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => $request->user()?->id,
                    'ip' => $request->ip(),
                    'correlation_id' => $correlationId,
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'http_error',
                    'message' => $status >= 500
                        ? 'Внутренняя ошибка сервера. Пожалуйста, обратитесь к администратору.'
                        : 'Ошибка обработки запроса',
                    'correlation_id' => $correlationId,
                ], $status);
            }

            // Страница под статус, если есть; иначе — заглушка 500
            $view = view()->exists("errors.{$status}") ? "errors.{$status}" : 'errors.500';
            return response()->view($view, [], $status);
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
                    'correlation_id' => $correlationId,
                ], 500);
            }

            $adminAlertService = new AdminAlertService();
            $adminAlertService->sendMsgToAdmin('Ошибка 500', 'Ошибка 500 в логе');

            return response()->view('errors.500', [], 500);
        });
    }
}
