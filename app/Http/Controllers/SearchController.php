<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $searcService;

    public function __construct()
    {
        $this->searcService = new SearchService();

    }

    public function search(Request $request)
    {
        $this->destroySession();

        $filters = $request->validate([
            'for_events' => 'nullable|string', // теперь строка, а не массив
            'district' => 'nullable|array',
            'capacity_to' => 'nullable|integer',
            'per_person' => 'nullable|integer',
            'features' => 'nullable|array',
        ]);

        // Сохраняем выбранные значения в сессии
        session()->put('selected_filters', $filters);

        return $this->searchResults($request);

    }

    public function clearFilters(Request $request)
    {

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
    }


    public function searchResults(Request $request)
    {
        $data = $this->searcService->searchResults($request);

        return \view('front', ['data' => $data['data'], 'pagination' => $data['pagination']]);
    }

    public function destroySession()
    {
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
        return empty(session('selected_filters')) &&
            empty(session('for_events')) &&
            empty(session('features')) &&
            empty(session('district'));
    }


}
