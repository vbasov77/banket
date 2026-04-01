<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\UserCity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CityController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCities(Request $request): JsonResponse
    {
        $cities = City::all(); // Или другой запрос для получения городов
        return response()->json(['cities' => $cities]);
    }

    public function setCity(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string',
            'city_id' => 'required|integer',
        ]);

        $cityName = $validated['city'];
        $cityId = $validated['city_id'];

        Session::forget('selected_filters');

        // Сохраняем в сессию
        Session::put('user_city', $cityName);
        $request->session()->save();

        // Если пользователь не авторизован, возвращаем успех без работы с БД
        if (!Auth::check()) {
            return response()->json(['success' => true]);
        }

        try {
            // Используем updateOrCreate для упрощения логики
            UserCity::updateOrCreate(
                ['user_id' => Auth::id()],
                ['city_id' => $cityId]
            );

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Ошибка при сохранении города пользователя: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при сохранении города'
            ], 500);
        }
    }

    public function getDistrictsByCity(Request $request)
    {
        $cityName = session('user_city', 'Санкт-Петербург');

        // Получаем ID города
        $city = City::where('name', $cityName)->first();


        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'Город не найден'
            ], 404);
        }

        // Получаем районы для этого города
        $districts = District::where('city_id', $city->id)
            ->select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'districts' => $districts
        ]);
    }


}
