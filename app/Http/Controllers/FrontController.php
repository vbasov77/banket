<?php

declare(strict_types=1);

namespace App\Http\Controllers;


use App\Services\ObjService;
use App\Services\UserCityService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Factory;


class FrontController extends Controller
{
    private ObjService $objService;
    protected UserCityService $userCityService;

    /**
     * @param ObjService $objService
     * @param UserCityService $userCityService
     */
    public function __construct(ObjService $objService, UserCityService $userCityService)
    {
        $this->objService = $objService;
        $this->userCityService = $userCityService;
    }

    /**
     * @return Application|Factory|View|Response
     */
    public function show(Request $request): Application|Factory|View|Response
    {

        $this->userCityService->checkSessionUserCity($request);
        $message = $request->message ?? null;

        try {
            // 1. Параметры из URL (пагинация, явные фильтры)
            $requestFilters = $request->only([
                'for_events', 'capacity_to', 'per_person', 'features', 'district'
            ]);

            // 2. Фильтры из сессии — гарантированно делаем массивом, даже если их нет
            $sessionFilters = (array) session('selected_filters', []);

            // 3. Объединяем: приоритет у URL, остальное — из сессии
            $mergedFilters = array_merge($sessionFilters, array_filter($requestFilters));

            // 4. Временно внедряем фильтры в запрос
            $originalInput = $request->all();
            $request->merge($mergedFilters);

            // Вызываем сервис (он по-прежнему принимает только Request)
            $data = $this->objService->findObjsWithDetails($request);

            // Восстанавливаем исходный запрос (чтобы не ломать другую логику ниже)
            $request->replace($originalInput);

            return view('front', [
                'data' => $data,
                'message' => $message,
            ]);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в FrontController@show: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->view('errors.500', [], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в FrontController@show: ' . $e->getMessage(),
                [
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            return response()->view('errors.500', [], 500);
        }
    }


}