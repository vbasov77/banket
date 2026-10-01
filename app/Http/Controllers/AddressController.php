<?php

namespace App\Http\Controllers;

use App\Services\MapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\City;
use App\Models\District;
use App\Models\AddressSubj;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{

    private MapService $mapService;


    // Поиск адреса через DaData

    /**
     * @param MapService $mapService
     */
    public function __construct(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    public function suggest(Request $request)
    {
        $query = $request->input('q', '');

        if (mb_strlen($query, 'UTF-8') < 3) {
            return response()->json([]);
        }

        try {
            $apiKey = config('services.dadata.api_key');

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
                'query' => $query,
                'count' => 10,
            ]);

            if (!$response->successful()) {
                Log::channel('error_file')->error('DaData API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Ошибка DaData: ' . $response->status()], 500);
            }

            $data = $response->json();
            $suggestions = $data['suggestions'] ?? [];

            $result = [];
            foreach ($suggestions as $item) {
                $dataItem = $item['data'] ?? [];

                // ВАЖНО: добавляем street_type, который нужен фронтенду
                $streetType = $dataItem['street_type'] ?? '';
                $streetName = $dataItem['street'] ?? '';

                // Опционально: можно сразу склеить на бэкенде, если не хочешь делать это на JS
                // $streetFull = $streetType ? "{$streetType} {$streetName}" : $streetName;

                $result[] = [
                    'value'          => $item['value'] ?? '',
                    'city'           => $dataItem['city'] ?? $dataItem['settlement'] ?? '',
                    'area'           => $dataItem['city_district'] ?? $dataItem['area'] ?? '',
                    'street'         => $streetName,
                    'street_type'    => $streetType, // <-- ЭТОГО НЕ ХВАТАЛО
                    // 'street_full'  => $streetFull,  // можно добавить, если хочешь
                    'house'          => $dataItem['house'] ?? '',
                    'lat'            => $dataItem['geo_lat'] ?? null,
                    'lon'            => $dataItem['geo_lon'] ?? null,
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::channel('error_file')->error('DaData request failed', [
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Сбой при запросе к DaData'], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'city_name' => 'required|string|max:255',
                'district_name' => 'required|string|max:255',
                'street' => 'nullable|string|max:255',
                'houseNumber' => 'nullable|string|max:50',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'subj_id' => 'required|integer|exists:subjs,id',
                'obj_id' => 'required|integer',
            ]);

            // 1. Находим или создаём город
            $city = City::firstOrCreate(['name' => $validated['city_name']]);

            // 2. Находим или создаём район
            $district = District::firstOrCreate(
                ['city_id' => $city->id],
                ['name' => $validated['district_name'] ?: 'Не указан']
            );

            // 3. Формируем массив для старого сервиса
            $serviceData = [
                'city_id' => $city->id,
                'district_id' => $district->id,
                'street' => $validated['street'],
                'houseNumber' => $validated['houseNumber'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'subj_id' => $validated['subj_id'],
                'obj_id' => $validated['obj_id'],
            ];

            // 4. Вызываем старый сервис
            $user = auth()->user();
            $result = $this->mapService->addSubjectToMap(
                $serviceData,
                $user->id,
                $user->isAdmin()
            );

            return response()->json($result, $result['code'] ?? 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in AddressController@store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при сохранении точки'
            ], 500);
        }
    }
}
