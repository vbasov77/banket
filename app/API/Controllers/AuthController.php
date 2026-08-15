<?php

namespace App\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Используем стандартный Auth::attempt — он сам проверит хеш пароля
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Неверный логин или пароль'], 401);
        }

        $user = Auth::user();

        // Удаляем старые токены пользователя (опционально: чтобы на устройстве был только 1 активный)
        $user->tokens()->delete();

        // Создаём настоящий токен Sanctum и получаем его как строку
        $token = $user->createToken('android_app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
            ],
        ], 200);
    }
}
