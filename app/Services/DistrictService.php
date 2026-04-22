<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Models\District;


class DistrictService
{
    public function getDistrictsByCity(string $cityName = null): array
    {
        // Логика получения названия города (из сессии, базы или установка по умолчанию)
        $cityName = $this->resolveCityName($cityName);

        // Получение ID города
        $city = $this->findCityByIdOrName($cityName);

        if (!$city) {
            return [
                'success' => false,
                'message' => 'Город не найден: ' . $cityName,
                'code' => 404,
            ];
        }

        // Получение районов для города
        $districts = $this->getCityDistricts($city->id);

        return [
            'success' => true,
            'city_id' => $city->id,
            'city_name' => $cityName,
            'districts' => $districts,
            'total' => count($districts),
        ];
    }

    protected function resolveCityName(?string $cityName): string
    {
        // Если город передан явно — используем его
        if ($cityName) {
            return $cityName;
        }

        // Проверяем сессию
        $cityName = Session::get('user_city');

        // Если нет в сессии — ищем в базе
        if (!$cityName && Auth::check()) {
            $userCity = DB::table('user_city')
                ->where('user_id', Auth::id())
                ->join('cities', 'user_city.city_id', '=', 'cities.id')
                ->select('cities.name as city_name')
                ->first();

            if ($userCity) {
                $cityName = $userCity->city_name;
                Session::put('user_city', $cityName);
            }
        }

        // Устанавливаем по умолчанию, если не нашли
        return $cityName ?? 'Санкт-Петербург';
    }

    protected function findCityByIdOrName(string $cityName): ?City
    {
        return City::where('name', $cityName)->first();
    }

    protected function getCityDistricts(int $cityId): array
    {
        return District::where('city_id', $cityId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
