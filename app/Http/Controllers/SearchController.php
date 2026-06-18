<?php

namespace App\Http\Controllers;

use App\Http\Requests\Search\SearchRequest;
use App\Services\SearchService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Factory;

class SearchController extends Controller
{
    private SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;

    }

    /**
     * @return JsonResponse
     *
     */
    public function clearFilters(): JsonResponse
    {
        try {
            $isCleared = $this->destroySession();

            return response()->json([
                'success' => $isCleared,
                'message' => $isCleared ? 'Фильтры сброшены' : 'Ошибка сброса фильтров',
                'debug' => [
                    'selected_filters' => session('selected_filters'),
                    'for_events' => session('for_events'),
                    'district' => session('district'),
                    'per_person' => session('per_person'),
                    'features' => session('features'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in ObjectsController@clearFilters: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    // Добавляем состояние сессии на момент ошибки для отладки
                    'session_state_before_error' => [
                        'selected_filters' => session('selected_filters'),
                        'for_events' => session('for_events'),
                        'district' => session('district'),
                        'per_person' => session('per_person'),
                        'features' => session('features'),
                    ]
                ]
            );

            // Возвращаем JSON с ошибкой
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сбросе фильтров',
                'debug' => [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'session_cleared_attempt' => true,
                    // Фиксируем текущее состояние сессии после попытки сброса
                    'session_state_after_attempt' => [
                        'selected_filters' => session('selected_filters'),
                        'for_events' => session('for_events'),
                        'district' => session('district'),
                        'per_person' => session('per_person'),
                        'features' => session('features'),
                    ]
                ]
            ], 500);
        }
    }


    /**
     * @param SearchRequest $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function search(SearchRequest $request): Application|Factory|View|RedirectResponse
    {
        try {
            $this->destroySession();

            // Валидация уже выполнена в SearchRequest
            $filters = $request->validated();

            // Сохраняем выбранные значения в сессии
            session()->put('selected_filters', $filters);

            $data = $this->searchService->searchResults($request);
            $arrayDistricts = null;
            if(!empty(count(session('selected_filters')['district'])) > 0){
                $arrayDistricts = session('selected_filters')['district'];
            }

            return view('front', [
                'data' => $data['data'],
                'pagination' => $data['pagination'],
                'arrayDistricts' => $arrayDistricts
            ]);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in ObjectsController@search: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'filters' => $request->all(),
                    'sql' => $e->getSql()
                ]
            );
            return redirect()->route('front')->with('error', 'Database error occurred during search');
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in ObjectsController@search: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'filters' => $request->all()
                ]
            );
            return redirect()->route('front')->with('error', 'An error occurred during search');
        }
    }


    /**
     * @return bool
     */
    public function destroySession(): bool
    {
        try {
            // Проверяем доступность сессии перед работой с ней
            if (!session()->isStarted()) {
                Log::channel('error_file')->error(
                    'Session is not started in ObjectsController@destroySession'
                );
                return false;
            }

            // 1. Полностью удаляем ключ из сессии
            session()->forget('selected_filters');

            // 2. Дополнительно сбрасываем все связанные данные
            session([
                'selected_filters' => null,
                'for_events' => null,
                'district' => [],
                'per_person' => null,
                'capacity_to' => null,
                'features' => [],
            ]);

            // 3. Явно регенерируем ID сессии (опционально, для полной очистки)
            session()->regenerate();

            // 4. Проверяем, что данные действительно удалены
            $isSelectedFiltersEmpty = empty(session('selected_filters'));
            $isForEventsEmpty = empty(session('for_events'));
            $isFeaturesEmpty = empty(session('features'));
            $isDistrictEmpty = empty(session('district'));

            $allCleared = $isSelectedFiltersEmpty &&
                $isForEventsEmpty &&
                $isFeaturesEmpty &&
                $isDistrictEmpty;

            // Если какие‑то данные не удалились, логируем это
            if (!$allCleared) {
                Log::channel('error_file')->error(
                    'Failed to fully clear session data in ObjectsController@destroySession',
                    [
                        'remaining_data' => [
                            'selected_filters' => session('selected_filters'),
                            'for_events' => session('for_events'),
                            'district' => session('district'),
                            'per_person' => session('per_person'),
                            'capacity_to' => session('capacity_to'),
                            'features' => session('features'),
                        ]
                    ]
                );
            }

            return $allCleared;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in ObjectsController@destroySession: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    // Сохраняем состояние сессии на момент ошибки
                    'session_state_at_error' => [
                        'selected_filters' => session('selected_filters'),
                        'for_events' => session('for_events'),
                        'district' => session('district'),
                        'per_person' => session('per_person'),
                        'capacity_to' => session('capacity_to'),
                        'features' => session('features'),
                    ]
                ]
            );
            return false;
        }
    }


}
