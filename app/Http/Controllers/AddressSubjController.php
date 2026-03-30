<?php

namespace App\Http\Controllers;

use App\Models\AddressSubj;
use App\Models\City;
use App\Models\District;
use App\Models\GroupAddressObj;
use App\Models\MapPoint;
use App\Models\Subj;
use App\Services\MapService;
use App\Services\SubjService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddressSubjController extends Controller
{
    private $mapService;
    private $subjService;

    public function __construct()
    {
        $this->mapService = new MapService();
        $this->subjService = new SubjService();
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
//
//        // Валидация входных данных
//        if (!$query || strlen($query) < 2) {
//            return response()->json(['error' => 'Запрос слишком короткий'], 400);
//        }

        try {
            // Поиск городов в локальной базе данных
            $cities = City::select('id', 'name')
                ->where('name', 'like', '%' . $query . '%') // регистронезависимый поиск
                // Альтернатива для MySQL: ->where('name', 'like', '%' . $query . '%')
                ->orderBy('name') // сортировка по названию
                ->limit(10) // ограничение количества результатов
                ->get()
                ->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                    ];
                });

            if ($cities->isEmpty()) {
                return response()->json(['error' => 'Города не найдены'], 404);
            }

            return response()->json($cities);

        } catch (\Exception $e) {
            Log::error('Local city search error: ' . $e->getMessage());
            return response()->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // Вспомогательный метод для извлечения названия города из данных Nominatim
    // (оставляем на случай, если понадобится вернуть API-поиск в будущем)
    private function extractCityName(array $place): ?string
    {
        // Логика извлечения названия города
        return $place['address']['city'] ??
            $place['address']['town'] ??
            $place['address']['village'] ??
            null;
    }


    public function searchStreets(Request $request)
    {
        $city = $request->input('city');
        $query = $request->input('q');

        // Валидация
        if (!$city || !$query || strlen($query) < 2) {
            return response()->json(['error' => 'Город и запрос обязательны, минимум 2 символа'], 400);
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'MyCitySearchApp/1.0 (0120912@mail.ru)',
                    'Accept' => 'application/json'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => "{$query}, {$city}", // Поиск улицы в контексте города
                    'countrycodes' => 'RU',
                    'format' => 'json',
                    'addressdetails' => '1',
                    'limit' => 5,
                    'featuretype' => 'street' // Только улицы
                ]);

            if (!$response->successful()) {
                Log::error('Nominatim API error for streets: ' . $response->status());
                return response()->json(['error' => 'API временно недоступно'], 503);
            }

            $data = $response->json();
            $streets = [];
            $uniqueStreets = [];

            foreach ($data as $place) {
                $streetName = $this->extractStreetName($place);
                if ($streetName && !in_array($streetName, $uniqueStreets)) {
                    $uniqueStreets[] = $streetName;
                    $streets[] = [
                        'name' => $streetName,
                        'lat' => $place['lat'],
                        'lon' => $place['lon']
                    ];
                }
            }

            return response()->json($streets);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Nominatim for streets: ' . $e->getMessage());
            return response()->json(['error' => 'Проблема с подключением к сервису'], 503);
        } catch (\Exception $e) {
            Log::error('Server error for streets: ' . $e->getMessage());
            return response()->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    public function searchDistricts(Request $request)
    {
        $cityId = $request->input('city_data_city_id');
        $query = $request->input('q');

        // Валидация
        if (!$query || strlen($query) < 2) {
            return response()->json(['error' => 'Запрос обязателен, минимум 2 символа'], 400);
        }

        try {
            $districts = District::select('id', 'name')
                ->where('city_id', $cityId)
                ->where('name', 'like', '%' . $query . '%') // регистронезависимый поиск
                // Альтернатива для MySQL: ->where('name', 'like', '%' . $query . '%')
                ->orderBy('name') // сортировка по названию
                ->limit(10) // ограничение количества результатов
                ->get()
                ->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                    ];
                });

            if ($districts->isEmpty()) {
                return response()->json(['error' => 'Города не найдены'], 404);
            }

            return response()->json($districts);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Nominatim for districts: ' . $e->getMessage());
            return response()->json(['error' => 'Проблема с подключением к сервису'], 503);
        } catch (\Exception $e) {
            Log::error('Server error for districts: ' . $e->getMessage());
            return response()->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    /**
     * Извлекает название района из данных Nominatim
     * @param array $place Данные места от Nominatim
     * @return string|null Название района или null, если не найдено
     */
    private function extractDistrictName(array $place): ?string
    {
        // Пробуем разные варианты расположения информации о районе
        $address = $place['address'] ?? [];

        // Основные ключи для поиска района
        $possibleKeys = [
            'suburb',      // пригород, район
            'district',   // административный район
            'quarter',   // квартал/район
            'neighbourhood' // район/окрестность
        ];

        foreach ($possibleKeys as $key) {
            if (!empty($address[$key])) {
                return $address[$key];
            }
        }

        // Если не нашли в address, проверяем extratags
        if (!empty($place['extratags']['district'])) {
            return $place['extratags']['district'];
        }

        return null;
    }


    private function extractStreetName($place)
    {
        $address = $place['address'] ?? [];

        if (isset($address['road'])) {
            return $address['road'];
        } elseif (isset($address['pedestrian'])) {
            return $address['pedestrian'];
        } elseif (isset($address['street'])) {
            return $address['street'];
        } else {
            // Пытаемся извлечь из display_name
            $displayName = $place['display_name'] ?? '';
            $parts = explode(',', $displayName);

            // Ищем часть, содержащую «улица», «проспект» и т. д.
            $streetKeywords = ['улица', 'проспект', 'бульвар', 'шоссе', 'переулок', 'набережная'];
            foreach ($parts as $part) {
                foreach ($streetKeywords as $keyword) {
                    if (stripos($part, $keyword) !== false) {
                        return trim($part);
                    }
                }
            }
            return null;
        }
    }

    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'house_number' => 'required|string|max:50',
            'coordinates.lat' => 'required|numeric',
            'coordinates.lon' => 'required|numeric'
        ]);

        try {
            // Здесь сохраняем данные в БД
            // Например:
            // Address::create($validated);

            Log::info('Address saved successfully', $validated);

            return response()->json([
                'success' => true,
                'message' => 'Адрес успешно сохранён',
                'data' => $validated
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error saving address: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении адреса'
            ], 500);
        }
    }

}

