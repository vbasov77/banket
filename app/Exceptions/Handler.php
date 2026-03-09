<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // для Продакт
        $this->renderable(function (Throwable $exception, Request $request) {
            // Логируем ошибку для отладки
            Log::error('Unhandled exception: ' . $exception->getMessage(), [
                'exception' => $exception,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
            ]);

            // Для AJAX‑запросов возвращаем JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Произошла ошибка',
                    'message' => 'Пожалуйста, попробуйте ещё раз',
                ], 500);
            }

//             Для обычных запросов показываем красивую страницу
//            return response()->view('errors.500', [], 500);
        });
    }
}
