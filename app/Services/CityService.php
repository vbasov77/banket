<?php

namespace App\Services;

use App\Models\City;
use App\Models\UserCity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CityService
{
    const DEFAULT_CITY_ID = 1; // ID Санкт‑Петербурга в базе

    /**
     * @return City
     */
    public function getUserCity(): City
    {
        // Если пользователь авторизован
        if (Auth::check()) {
            // Сначала проверяем сессию
            $cityId = Session::get('user_city');

            if ($cityId) {
                return City::findOrFail($cityId);
            }

            // Если в сессии нет — ищем в связующей таблице
            $userCity = Auth::user()->city()->first();
            if ($userCity) {
                // Сохраняем в сессию для будущих запросов
                Session::put('user_city_id', $userCity->id);
                return $userCity;
            }
        }

        // Для неавторизованных пользователей
        $cityId = Session::get('guest_city_id');
        if ($cityId) {
            return City::findOrFail($cityId);
        }

        // Устанавливаем Санкт‑Петербург по умолчанию
        $defaultCity = $this->getDefaultCity();
        Session::put('guest_city_id', $defaultCity->id);
        return $defaultCity;
    }

    /**
     * @param int $cityId
     * @return void
     */
    public function setUserCity(int $cityId): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Очищаем предыдущие связи и добавляем новую
            $user->city()->sync([$cityId]);

            // Обновляем сессию
            Session::put('user_city_id', $cityId);
        } else {
            Session::put('guest_city_id', $cityId);
        }
    }

    /**
     * @return City
     */
    private function getDefaultCity(): City
    {
        return City::findOrFail(self::DEFAULT_CITY_ID);
    }

    /**
     * @return Collection
     */
    public function getAllCities(): Collection
    {
        return City::all();
    }

    public function findUserCity(Request $request)
    {
        if (Auth::check()) {
            $city = UserCity::where('user_id', Auth::user()->id)->first();
            if ($city !== null) {
                $nameCity = City::where('id', $city->city_id)->value('name');
                $request->session()->put('user_city', $nameCity);
                $request->session()->save();
            }
        }
    }
}
