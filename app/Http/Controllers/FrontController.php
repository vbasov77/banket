<?php

declare(strict_types=1);

namespace App\Http\Controllers;


use App\Services\IpService;
use App\Services\ObjService;
use App\Services\UserCityService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Factory;


class FrontController extends Controller
{
    private ObjService $objService;
    protected UserCityService $userCityService;
    protected IpService $ipService;

    /**
     * @param ObjService $objService
     * @param UserCityService $userCityService
     */
    public function __construct(ObjService      $objService,
                                UserCityService $userCityService,
                                IpService       $ipService)
    {
        $this->objService = $objService;
        $this->userCityService = $userCityService;
        $this->ipService = $ipService;
    }

    /**
     * @return Application|Factory|View|Response
     */
    public function show(Request $request): Application|Factory|View|Response
    {

        $this->userCityService->checkSessionUserCity($request);
        $message = $request->message ?? null;

        $ip = $request->ip();
        if (!$this->ipService->checkIp($ip)) {
            $this->ipService->store($ip);
        }

        try {
            // 1. Параметры из URL (пагинация, явные фильтры)
            $requestFilters = $request->only([
                'for_events', 'capacity_to', 'per_person', 'district', 'near_metro_id'
            ]);

            // 2. Фильтры из сессии — гарантированно делаем массивом, даже если их нет
            $sessionFilters = (array)session('selected_filters', []);

            // 3. Объединяем: приоритет у URL, остальное — из сессии
            $mergedFilters = array_merge($sessionFilters, array_filter($requestFilters));

            // 4. Временно внедряем фильтры в запрос
            $originalInput = $request->all();
            $request->merge($mergedFilters);

            // Вызываем сервис (он по-прежнему принимает только Request)
            $data = $this->objService->findObjsWithDetails($request);
//            dd(session('user_city'), session('city_id'), session('region_ids'), $data);
            $metaDescription = $this->findMetaDescription($data);


            // Восстанавливаем исходный запрос (чтобы не ломать другую логику ниже)
            $request->replace($originalInput);
            return view('front', [
                'data' => $data,
                'message' => $message,
                'metaDescription' => $metaDescription
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
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
            return response()->view('errors.500', [], 500);
        }
    }

    private function findMetaDescription(mixed $objData): string
    {
        // Исправленная логика: items() -> collect()
        if ($objData instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $collection = collect($objData->items());
        } else {
            $collection = collect($objData);
        }
        $city = session('user_city');
        if (is_array($city)) {
            $city = implode(' и ', $city);
        }
        if (!$city) {
            $city = 'Санкт-Петербург';
        }

        if ($collection->isEmpty()) {
            return 'Банкетные залы и рестораны для свадеб, корпоративов и дней рождений в городе ' . $city . '. Бронирование онлайн.';
        }

        // Сбор цен и вместимости оставляем — это сильные триггеры
        $prices = $collection->map(function ($obj) {
            $subj = collect($obj['subjs_data'] ?? [])->first();
            if (!$subj) return null;
            if (!empty($subj['minimum_cost'])) return (int)$subj['minimum_cost'];
            if (!empty($subj['per_person'])) return (int)($subj['per_person'] * 10);
            return null;
        })->filter()->values();

        $capacities = $collection->map(function ($obj) {
            $subj = collect($obj['subjs_data'] ?? [])->first();
            return $subj && !empty($subj['capacity_to']) ? (int)$subj['capacity_to'] : null;
        })->filter()->values();

        // Формируем части БЕЗ количества
        $parts = ['Широкий выбор банкетных залов в городе' . $city];

        if ($prices->isNotEmpty()) {
            $min = $prices->min();
            $max = $prices->max();
            $parts[] = $min === $max
                ? 'от ' . number_format($min, 0, '.', ' ') . ' ₽'
                : 'от ' . number_format($min, 0, '.', ' ') . ' до ' . number_format($max, 0, '.', ' ') . ' ₽';
        }

        if ($capacities->isNotEmpty()) {
            $min = $capacities->min();
            $max = $capacities->max();
            $parts[] = $min === $max
                ? 'на ' . $min . ' гостей'
                : 'на ' . $min . '–' . $max . ' гостей';
        }

        $parts[] = 'полный сервис, бронирование онлайн';

        $fullDescription = implode(', ', $parts);

        if (mb_strlen($fullDescription, 'UTF-8') > 160) {
            $fullDescription = mb_substr($fullDescription, 0, 157, 'UTF-8') . '...';
        }

        return $fullDescription;
    }


}