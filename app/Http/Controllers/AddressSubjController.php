<?php

namespace App\Http\Controllers;

use App\Models\AddressSubj;
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

        // Валидация входных данных
        if (!$query || strlen($query) < 2) {
            return response()->json(['error' => 'Запрос слишком короткий'], 400);
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'MyCitySearchApp/1.0 (0120912@mail.ru)',
                    'Accept' => 'application/json'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'countrycodes' => 'RU',
                    'format' => 'json',
                    'addressdetails' => '1',
                    'limit' => 10,
                    'featuretype' => 'city,town,village' // Ограничиваем типы объектов
                ]);

            if (!$response->successful()) {
                Log::error('Nominatim API error: ' . $response->status());
                return response()->json(['error' => 'API временно недоступно'], 503);
            }

            $data = $response->json();
            $cities = [];
            $processedNames = [];

            // Приоритет типов: город > посёлок > деревня > прочее
            $typePriority = [
                'city' => 1,
                'town' => 2,
                'village' => 3,
            ];

            foreach ($data as $place) {
                $cityName = $this->extractCityName($place);
                if (!$cityName) continue;

                $currentType = $place['type'] ?? 'other';
                $currentPriority = $typePriority[$currentType] ?? 99;

                if (!isset($processedNames[$cityName])) {
                    // Первый встреченный вариант для этого названия
                    $processedNames[$cityName] = $currentPriority;
                    $cities[] = [
                        'name' => $cityName,
                        'lat' => $place['lat'],
                        'lon' => $place['lon'],
                        'type' => $currentType
                    ];
                } elseif ($processedNames[$cityName] > $currentPriority) {
                    // Заменяем на вариант с более высоким приоритетом
                    $index = array_search($cityName, array_column($cities, 'name'));
                    if ($index !== false) {
                        $cities[$index] = [
                            'name' => $cityName,
                            'lat' => $place['lat'],
                            'lon' => $place['lon'],
                            'type' => $currentType
                        ];
                        $processedNames[$cityName] = $currentPriority;
                    }
                }
            }

            // Удаляем поле 'type' из итогового ответа (не нужно фронтенду)
            foreach ($cities as &$city) {
                unset($city['type']);
            }

            return response()->json($cities);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Nominatim: ' . $e->getMessage());
            return response()->json(['error' => 'Проблема с подключением к сервису'], 503);
        } catch (\Exception $e) {
            Log::error('Server error: ' . $e->getMessage());
            return response()->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    private function extractCityName($place)
    {
        $address = $place['address'] ?? [];

        // Проверяем, что это действительно город/посёлок
        if (isset($address['city'])) {
            return $address['city'];
        } elseif (isset($address['town'])) {
            return $address['town'];
        } elseif (isset($address['village'])) {
            return $address['village'];
        } else {
            // Исключаем станции, улицы и т. д.
            $excludeKeywords = ['station', 'street', 'area', 'district', 'railway', 'square'];
            $displayName = $place['display_name'] ?? '';

            foreach ($excludeKeywords as $keyword) {
                if (stripos($displayName, $keyword) !== false) {
                    return null; // Не считаем это городом
                }
            }

            $parts = explode(',', $displayName);
            return trim($parts[0]) ?? null;
        }
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

