<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\UserCity;
use App\Services\CityService;
use App\Services\DistrictService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CityController extends Controller
{
    protected CityService $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }
    /**
     *
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
            $districtService = new DistrictService();
            $result = $districtService->getDistrictsByCity();

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
