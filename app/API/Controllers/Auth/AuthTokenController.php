<?php

namespace App\API\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
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
            'device_model' => ['nullable', 'string', 'max:64'],
        ]);

        Log::channel('info_file')->info([$request->token]);
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not authorized',
            ], 401);
        }

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'type' => 'fcm',
                'device_model' => $request->device_model,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'ok',
            'user_id' => $user->id,
        ]);
    }
}
