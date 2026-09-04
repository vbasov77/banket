<?php

namespace App\API\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthTokenController extends Controller
{
    /**
     * Сохранение FCM-токена пользователя.
     * Вызывается из Android после логина.
     */
    public function saveFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not authorized',
            ], 401);
        }

        // Используем fcm_token, как в твоей миграции users
        $user->update([
            'fcm_token' => $request->token,
        ]);

        return response()->json([
            'status' => 'ok',
            'user_id' => $user->id,
        ]);
    }
}
