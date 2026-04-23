<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\UserCity;
use App\Services\CityService;
use App\Services\DistrictService;
use App\Services\UserCityService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class UserCityController extends Controller
{
    protected UserCityService $userCityService;
    protected DistrictService $districtService;

    public function __construct(UserCityService $userCityService, DistrictService $districtService)
    {
        $this->userCityService = $userCityService;
        $this->districtService = $districtService;
    }

    /**
     * Получение списка городов для фильтра в панели навигации
     * @return JsonResponse
     */
    public function getUserCity(): JsonResponse
    {
        $result = $this->userCityService->findUserCity();
        return response()->json(
            $result,
            $result['http_status'] ?? 200
        );
    }



}
