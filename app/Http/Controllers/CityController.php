<?php

namespace App\Http\Controllers;

use App\Services\CityService;
use App\Services\DistrictService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CityController extends Controller
{
    protected CityService $cityService;
    protected DistrictService $districtService;

    public function __construct(CityService $cityService, DistrictService $districtService)
    {
        $this->cityService = $cityService;
        $this->districtService = $districtService;
    }

    /**
     * Получение списка городов для фильтра в панели навигации
     * @return JsonResponse
     */
    public function getCities(): JsonResponse
    {
        $result = $this->cityService->getCities();
        return response()->json(
            $result,
            $result['http_status'] ?? 200
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setCity(Request $request): JsonResponse
    {
        $result = $this->cityService->setCity($request);

        return response()->json(
            $result,
            $result['http_status'] ?? 200
        );
    }

    /**
     * @return JsonResponse
     */
    public function getDistrictsByCity(): JsonResponse
    {
        try {
            $result = $this->districtService->getDistrictsByCity();
            return response()->json($result, $result['code'] ?? 200);

        } catch (QueryException $e) {
            Log::critical('Ошибка БД в getDistrictsByCity', [
                'city_name' => Session::get('user_city') ?? 'unknown',
                'user_id' => auth()->id(),
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'database_error',
                'message' => 'Ошибка при получении районов. Пожалуйста, попробуйте позже.'
            ], 500);

        } catch (\Exception $e) {
            Log::critical('Неожиданная ошибка в getDistrictsByCity', [
                'city_name' => Session::get('user_city') ?? 'unknown',
                'user_id' => auth()->id(),
                'exception' => $e::class,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'internal_error',
                'message' => 'Произошла внутренняя ошибка. Обратитесь к администратору.'
            ], 500);
        }
    }


}
