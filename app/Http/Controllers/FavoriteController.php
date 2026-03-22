<?php

namespace App\Http\Controllers;

use App\Models\FavoriteSubj;
use App\Models\Subj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // Добавление ресторана в избранное
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => $user]);
        }

        $restaurant = Subj::find($request->id);
        if (!$restaurant) {
            return response()->json(['error' => 'Ресторан не найден'], 404);
        }

        // Проверяем, не добавлен ли уже
        $existing = FavoriteSubj::where('user_id', $user->id)
            ->where('subj_id', $request->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Уже в избранном'], 200);
        }

        FavoriteSubj::create([
            'user_id' => (int) $user->id,
            'subj_id' => (int) $request->id
        ]);

        return response()->json(['message' => 'Добавлено в избранное']);
    }

    // Удаление ресторана из избранного
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        $favorite = FavoriteSubj::where('user_id', $user->id)
            ->where('subj_id', $request->id)
            ->first();

        if (!$favorite) {
            return response()->json(['error' => 'Не найдено в избранном'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Удалено из избранного'], 201);
    }

    // Получение списка избранного пользователя
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        $favorites = $user->favoriteRestaurants()->with('primaryImg')->get()->toArray();

        return view('favorites.index', ['favorites' => $favorites]);
    }
}
