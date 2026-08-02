<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Zags;

class ZagsController extends Controller
{
    /**
     * Получить список станций метро для города.
     * Используется для асинхронной загрузки в JS-дропдауне.
     */
    public function byCity()
    {
        $userCityName = session('user_city');
        if (!$userCityName) {
            return response()->json([
                'success' => false,
                'message' => 'Город не определён',
            ]);
        }

        $city = City::where('name', $userCityName)->firstOrFail();

        $zags = Zags::where('city_id', $city->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'zags' => $zags,
        ]);
    }
}
