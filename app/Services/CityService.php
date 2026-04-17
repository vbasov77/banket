<?php

namespace App\Services;

use App\Exceptions\CitySearchException;
use App\Models\City;
use App\Models\UserCity;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
     * Поиск городов по запросу
     *
     * @param string $query Строка поиска (минимум 2 символа)
     * @param int $limit Максимальное количество результатов (по умолчанию 10)
     * @return array<array{id: int, name: string}>
     * @throws CitySearchException
     */
    public function findCity(string $query, int $limit = 10): array
    {
        // Валидация входных данных
        $cleanQuery = trim($query);

        if (empty($cleanQuery)) {
            throw new CitySearchException(
                'Запрос не может быть пустым',
                400
            );
        }

        if (strlen($cleanQuery) < 2) {
            throw new CitySearchException(
                'Запрос слишком короткий. Минимальная длина — 2 символа.',
                400
            );
        }

        // Защита от потенциально опасных символов (опционально)
        if (preg_match('/[^\p{L}\p{N}\s-]/u', $cleanQuery)) {
            throw new CitySearchException(
                'Запрос содержит недопустимые символы',
                422
            );
        }

        try {
            $cities = City::select('id', 'name')
                ->where('name', 'ilike', '%' . $cleanQuery . '%') // регистронезависимый поиск (PostgreSQL)
                // Для MySQL: ->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . $cleanQuery . '%'])
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                    ];
                })->toArray();

            return $cities;

        } catch (QueryException $e) {
            Log::channel('error_file')->error('Database error in CityService::findCity', [
                'query' => $cleanQuery,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw new CitySearchException(
                'Ошибка при поиске городов. Пожалуйста, попробуйте позже.',
                500
            );
        } catch (\Exception $e) {
            Log::channel('error_file')->critical('Unexpected error in CityService::findCity', [
                'query' => $cleanQuery,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw new CitySearchException(
                'Внутренняя ошибка сервиса поиска городов.',
                500
            );
        }
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
