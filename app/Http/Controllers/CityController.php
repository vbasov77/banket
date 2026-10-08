<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use App\Models\UserCity;
use App\Services\CityService;
use App\Services\DistrictService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

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

    public function index(): JsonResponse
    {
        $result = $this->cityService->getRegions();

        return response()->json(['regions' => $result['data']], $result['http_status']);
    }

    public function cities(Region $region): JsonResponse
    {
        try {
            $result = $this->cityService->getCitiesByRegion($region);

            return response()->json([
                'success' => true,
                'cities' => $result['data'],
                'needs_city_choice' => $result['needs_city_choice'],
                'city' => $result['city'],
            ], $result['http_status']);
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Ошибка в CityController@cities', [
                'region_id' => $region->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера',
                'http_status' => 500,
            ], 500);
        }
    }

    public function setRegion(Request $request): JsonResponse
    {
        try {
            $result = $this->cityService->setRegion($request);

            return response()->json($result, $result['http_status']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors(),
                'http_status' => 422,
            ], 422);
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Ошибка в CityController@setRegion', [
                'request_data' => $request->all(),
                'exception_class' => get_class($e),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера',
                'http_status' => 500,
            ], 500);
        }
    }


    public function setCity(Request $request): array
    {
        try {
            $validated = $request->validate([
                'city_id' => ['required', 'integer', 'exists:cities,id'],
            ]);

            $city = City::with('region')->find($validated['city_id']);

            Session::forget('selected_filters');
            Session::put('user_city', $city->name);
            Session::put('city_id', $city->id);
            $request->session()->save();

            if (Auth::check()) {
                UserCity::updateOrCreate(
                    ['user_id' => Auth::id()],
                    ['city_id' => $city->id]
                );
            }

            return [
                'success' => true,
                'data' => [
                    'city' => $city->name,
                    'region_name' => $city->region->name,
                    'user_type' => Auth::check() ? 'user' : 'guest',
                ],
                'http_status' => 200,
            ];
        } catch (ValidationException $e) {
            return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $e->errors(), 'http_status' => 422];
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Ошибка в CityService::setRegion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Внутренняя ошибка сервера', 'http_status' => 500];
        }
    }




}
