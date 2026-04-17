<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;

class StreetSearchService extends Service
{
    /**
     * Основной метод поиска улиц
     */
    public function searchStreets(string $city, string $query): JsonResponse
    {
        try {
            $response = $this->makeApiRequest($city, $query);
            $data = $response->json();

            if (!$response->successful()) {
                return $this->handleApiError($response->status());
            }

            return $this->formatStreetsResponse($data);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Nominatim: ' . $e->getMessage());
            return response()->json(['error' => 'Проблема с подключением к сервису'], 503);
        } catch (\Exception $e) {
            Log::error('Server error: ' . $e->getMessage());
            return response()->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    /**
     * Выполняет запрос к Nominatim API
     */
    private function makeApiRequest(string $city, string $query): Response
    {
        $url = 'https://nominatim.openstreetmap.org/search';
        $params = [
            'q' => "{$query}, {$city}",
            'countrycodes' => 'RU',
            'format' => 'json',
            'addressdetails' => '1',
            'limit' => 5,
            'featuretype' => 'street'
        ];

        $client = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'MyCitySearchApp/1.0 (0120912@mail.ru)',
                'Accept' => 'application/json'
            ]);

        // Отключаем проверку SSL только в тестовой среде
        if (app()->environment('testing')) {
            $client->withOptions(['verify' => false]);
        }

        return $client->get($url, $params);
    }

    /**
     * Обрабатывает ошибки API
     */
    private function handleApiError(int $statusCode): JsonResponse
    {
        Log::error('Nominatim API error: ' . $statusCode);

        switch ($statusCode) {
            case 429:
                return response()->json(['error' => 'Превышен лимит запросов к API'], 429);
            case $statusCode >= 500:
                return response()->json(['error' => 'API временно недоступно'], 503);
            default:
                return response()->json(['error' => 'Ошибка API'], $statusCode);
        }
    }

    /**
     * Форматирует данные улиц для ответа
     */
    private function formatStreetsResponse($data): JsonResponse
    {
        // Дополнительная проверка типа данных
        if (!is_array($data)) {
            Log::warning('API returned non‑array data, returning empty array', [
                'data_type' => gettype($data),
                'data' => $data
            ]);
            return response()->json([]);
        }

        if (empty($data)) {
            Log::info('API returned empty array, returning empty JSON response');
            return response()->json([]);
        }

        $streets = [];
        $uniqueStreets = [];

        foreach ($data as $place) {
            // Проверяем, что $place — массив
            if (!is_array($place)) {
                Log::warning('Invalid place data in API response, skipping', ['place' => $place]);
                continue;
            }

            $streetName = $this->extractStreetName($place);

            if ($streetName && !in_array($streetName, $uniqueStreets)) {
                $uniqueStreets[] = $streetName;
                $streets[] = [
                    'name' => $streetName,
                    'lat' => $place['lat'] ?? null,
                    'lon' => $place['lon'] ?? null
                ];
            }
        }

        return response()->json($streets);
    }

    /**
     * Извлекает название улицы из данных API
     */
    private function extractStreetName(array $place): ?string
    {
        // Проверяем наличие необходимых ключей
        if (isset($place['address']['road'])) {
            return $place['address']['road'];
        }

        if (isset($place['display_name'])) {
            return $place['display_name'];
        }

        // Если ничего не найдено, возвращаем null
        return null;
    }

}
