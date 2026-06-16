<?php

namespace App\Http\Controllers;

use App\Exceptions\CitySearchException;
use App\Services\CityService;
use App\Services\StreetSearchService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AddressSubjController extends Controller
{
    private CityService $cityService;

    public function __construct(CityService $cityService)
    {

        $this->cityService = $cityService;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        // Input validation
        if (!$query || strlen($query) < 2) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => 'Query is too short',
                'details' => [
                    'field' => 'q',
                    'min_length' => 2,
                    'actual_length' => $query ? strlen($query) : 0
                ]
            ], 400);
        }

        try {
            // Search cities in local database
            $cities = $this->cityService->findCity($query);

            if (empty($cities)) { // Проверка на пустой массив
                return response()->json([], 404); // Возвращаем пустой массив при отсутствии результатов
            }

            return response()->json($cities); // Просто возвращаем массив без дополнительной обёртки

        } catch (QueryException $e) {
            Log::channel('error_file')->error('Database error in city search', [
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'error' => 'database_error',
                'message' => 'Error while searching for cities. Please try again later.',
                'correlation_id' => (string) Str::uuid()
            ], 500);

        } catch (CitySearchException $e) {
            Log::channel('error_file')->warning('City search business error', [
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_ip' => $request->ip()
            ]);

            return response()->json([
                'error' => 'search_error',
                'message' => $e->getMessage(),
                'correlation_id' => (string) Str::uuid()
            ], $e->getStatusCode() ?? 400);

        } catch (\Throwable $e) {
            Log::channel('error_file')->critical('Unexpected error in city search', [
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'correlation_id' => $correlationId = (string) Str::uuid()
            ]);

            return response()->json([
                'error' => 'internal_server_error',
                'message' => 'Internal server error. Please contact the administrator.',
                'correlation_id' => $correlationId
            ], 500);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchStreets(Request $request): JsonResponse
    {
        $city = $request->input('city');
        $query = $request->input('q');

        // Валидация
        if (!$city || !$query || mb_strlen($query, 'UTF-8') < 2) {
            return response()->json(['error' => 'Город и запрос обязательны, минимум 2 символа'], 400);
        }

        $streetSearchService = new StreetSearchService();
        return $streetSearchService->searchStreets($city, $query);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchDistricts(Request $request): JsonResponse
    {
        $service = new \App\Services\DistrictSearchService();
        $result = $service->search($request->all());

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], $result['code']);
        }

        return response()->json($result['data']);
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

