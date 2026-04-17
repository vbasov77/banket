<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Support\Facades\Log;

class DistrictSearchService extends Service
{
    public function search(array $params): array
    {
        $cityId = $params['city_data_city_id'] ?? null;
        $query = trim($params['q'] ?? '');

        // Валидация
        if (empty($query) || mb_strlen($query) < 2) {
            return [
                'success' => false,
                'error' => 'Запрос обязателен, минимум 2 символа',
                'code' => 400
            ];
        }

        try {
            $districts = District::select('id', 'name')
                ->where('city_id', $cityId)
                ->where('name', 'like', '%' . $query . '%')
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(function ($district) {
                    return [
                        'id' => $district->id,
                        'name' => $district->name,
                    ];
                })->toArray();

            if (empty($districts)) {
                return [
                    'success' => false,
                    'error' => 'Города не найдены',
                    'code' => 404
                ];
            }

            return [
                'success' => true,
                'data' => $districts
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Nominatim for districts: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Проблема с подключением к сервису',
                'code' => 503
            ];
        } catch (\Exception $e) {
            Log::error('Server error for districts: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Внутренняя ошибка сервера',
                'code' => 500
            ];
        }
    }
}
