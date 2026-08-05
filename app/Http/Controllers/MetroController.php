<?php

namespace App\Http\Controllers;

use App\Models\MetroStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class MetroController extends Controller
{
    /**
     * Получить список станций метро для города.
     * Используется для асинхронной загрузки в JS-дропдауне.
     */
    public function byCity(): JsonResponse
    {
        // Сначала пробуем взять city_id из сессии — это как у тебя в контроллерах поиска
        $cityId = Session::get('city_id');

        // Если в сессии нет — можно попробовать взять из GET-параметра (на всякий случай)
        if (!$cityId) {
            $cityId = request()->input('city_id');
        }

        if (!$cityId) {
            return response()->json([
                'success' => false,
                'message' => 'Город не определён',
            ]);
        }

        // Кэшируем список станций на 24 часа — они редко меняются
        $metros = Cache::remember("city:{$cityId}:metros", now()->addHours(24), function () use ($cityId) {
            return MetroStation::where('city_id', $cityId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });

        return response()->json([
            'success' => true,
            'metros'  => $metros->toArray(),
        ]);
    }
}
