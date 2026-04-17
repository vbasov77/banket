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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setCity(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'city' => 'required|string',
                'city_id' => 'required|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Ошибка валидации данных'
            ], 422);
        }

        $cityName = $validated['city'];
        $cityId = $validated['city_id'];

        Session::forget('selected_filters');
        Session::put('user_city', $cityName);
        $request->session()->save();

        if (!Auth::check()) {
            return response()->json(['success' => true]);
        }

        try {
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

    /**
     * @return JsonResponse
     */
    public function getDistrictsByCity(): JsonResponse
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Критическая ошибка в getDistrictsByCity:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Произошла ошибка',
                'message' => 'Пожалуйста, попробуйте ещё раз',
                'debug' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }


}
