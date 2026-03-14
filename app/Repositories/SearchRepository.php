<?php


namespace App\Repositories;


use App\Models\Obj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchRepository extends Repository
{


    public function search(Request $request)
    {
        // Получаем выбранные фильтры из сессии
        $selectedFilters = session('selected_filters', []);
        $forEventsFilter = $selectedFilters['for_events'] ?? [];

        // Начинаем построение запроса
        $query = Obj::with([
            'detailsObj' => function ($q) {
                $q->select('id', 'obj_id', 'for_events', 'kitchen', 'service',
                    'alcohol', 'payment_methods', 'text_obj');
            },
            'subjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person', 'capacity_to', 'site_type', 'features', 'text_subj');
            }
        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj');

        // Применяем фильтр по for_events, если есть выбранные значения
        if (!empty($forEventsFilter)) {
            $query->whereHas('detailsObj', function ($q) use ($forEventsFilter) {
                $q->whereJsonContains('for_events', $forEventsFilter);
            });
        }

        // Выполняем пагинацию
        $paginated = $query->paginate(6);

        // ДОПОЛНИТЕЛЬНО загружаем фото для каждого subj
        $paginated->getCollection()->transform(function ($obj) {
            $obj->subjs->transform(function ($subj) {
                // Загружаем первые 5 фото
                $subj->load([
                    'imgSubjFirst:subj_id,path',
                    'imgSubjs' => function ($q) {
                        $q->select('subj_id', 'path')
                            ->orderBy('position') // или 'id'
                            ->take(5);
                    }
                ]);

                return [
                    'id' => $subj->id,
                    'name_subj' => $subj->name_subj,
                    'minimum_cost' => $subj->minimum_cost,
                    'per_person' => $subj->per_person,
                    'capacity_to' => $subj->capacity_to,
                    'site_type' => $subj->site_type,
                    'features' => $subj->features,
                    'text_subj' => $subj->text_subj,
                    'path' => $subj->imgSubjFirst ? $subj->imgSubjFirst->path : null,
                    'image_paths' => $subj->imgSubjs->pluck('path')->toArray(), // теперь здесь будет до 5 фото
                ];
            });

            return [
                'obj_id' => $obj->id,
                'user_id' => $obj->user_id,
                'name_obj' => $obj->name_obj,
                'phone_obj' => $obj->phone_obj,
                'for_events' => $obj->detailsObj->for_events,
                'subjs_data' => $obj->subjs->toArray(),
            ];
        });

        return $paginated;
    }

    public function searchResults(Request $request)
    {
        // Получаем фильтры из сессии (сохраняются после POST‑запроса)
        $selectedFilters = session('selected_filters', []);
        $forEventsFilter = $selectedFilters['for_events'] ?? null;
        $capacityToFilter = $selectedFilters['capacity_to'] ?? null; // фильтр по вместимости
        $perPersonFilter = $selectedFilters['per_person'] ?? null; // фильтр по цене на человека
        $featuresFilter = $selectedFilters['features'] ?? null; // фильтр по features

        // Дополнительно проверяем, есть ли фильтры в текущем GET‑запросе (для пагинации)
        $currentForEvents = $request->input('for_events');
        if ($currentForEvents !== null) {
            $forEventsFilter = $currentForEvents;
        }

        $currentCapacityTo = $request->input('capacity_to');
        if ($currentCapacityTo !== null) {
            $capacityToFilter = $currentCapacityTo;
            // Сохраняем в сессию выбранный фильтр
            session(['selected_filters.capacity_to' => $capacityToFilter]);
        }

        // ОБРАБОТКА ФИЛЬТРА per_person ИЗ GET‑ЗАПРОСА
        $currentPerPerson = $request->input('per_person');
        if ($currentPerPerson !== null) {
            $perPersonFilter = $currentPerPerson;
            // Сохраняем в сессию выбранный фильтр
            session(['selected_filters.per_person' => $perPersonFilter]);
        }

        // ОБРАБОТКА ФИЛЬТРА features ИЗ GET‑ЗАПРОСА
        $currentFeatures = $request->input('features');
        if ($currentFeatures !== null) {
            $featuresFilter = $currentFeatures;
            // Сохраняем в сессию выбранный фильтр
            session(['selected_filters.features' => $featuresFilter]);
        }

        $query = Obj::with([
            'detailsObj' => function ($q) {
                $q->select('id', 'obj_id', 'for_events', 'kitchen', 'service',
                    'alcohol', 'payment_methods', 'text_obj');
            },
            'subjs' => function ($query) {
                $query->select(
                    'id',
                    'obj_id',
                    'name_subj',
                    'minimum_cost',
                    'per_person',
                    'capacity_to',
                    'site_type',
                    'features',
                    'text_subj'
                );
            }
        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj');

        // Применяем фильтр по for_events, если есть выбранное значение
        if (!is_null($forEventsFilter)) {
            $query->whereHas('detailsObj', function ($q) use ($forEventsFilter) {
                $q->whereJsonContains('for_events', $forEventsFilter);
            });
        }

        // ФИЛЬТРАЦИЯ по максимальной вместимости (capacity_to >= заданное значение)
        if (!is_null($capacityToFilter) && is_numeric($capacityToFilter)) {
            $query->whereHas('subjs', function ($q) use ($capacityToFilter) {
                $q->where('capacity_to', '>=', $capacityToFilter);
            });
        }

        // ФИЛЬТРАЦИЯ ПО ЦЕНЕ НА ЧЕЛОВЕКА (per_person <= заданное значение)
        if (!is_null($perPersonFilter) && is_numeric($perPersonFilter)) {
            $query->whereHas('subjs', function ($q) use ($perPersonFilter) {
                $q->where('per_person', '<=', $perPersonFilter);
            });
        }

        // ФИЛЬТРАЦИЯ ПО features (если есть выбранные значения)
        if (!is_null($featuresFilter) && is_array($featuresFilter) && !empty($featuresFilter)) {
            $query->whereHas('subjs', function ($q) use ($featuresFilter) {
                $q->where(function ($subQuery) use ($featuresFilter) {
                    foreach ($featuresFilter as $feature) {
                        $subQuery->orWhereJsonContains('features', $feature);
                    }
                });
            });
        }

        // Пагинация — 6 объектов на страницу
        $paginated = $query->paginate(6)->withQueryString();

        // Трансформация данных для каждого объекта
        $transformedData = $paginated->getCollection()->map(function ($obj) {
            // Преобразуем subjs в массив с нужной структурой
            $subjsData = $obj->subjs->map(function ($subj) {
                // Загружаем фото для каждого subj
                $subj->load([
                    'imgSubjFirst:subj_id,path',
                    'imgSubjs' => function ($q) {
                        $q->select('subj_id', 'path')
                            ->orderBy('position')
                            ->take(5);
                    }
                ]);

                return [
                    'id' => $subj->id,
                    'name_subj' => $subj->name_subj,
                    'minimum_cost' => $subj->minimum_cost,
                    'per_person' => $subj->per_person,
                    'capacity_to' => $subj->capacity_to,
                    'site_type' => $subj->site_type,
                    'features' => $subj->features,
                    'text_subj' => $subj->text_subj,
                    'path' => $subj->imgSubjFirst ? $subj->imgSubjFirst->path : null,
                    'image_paths' => $subj->imgSubjs->pluck('path')->toArray(),
                ];
            })->toArray();

            return [
                'obj_id' => $obj->id,
                'user_id' => $obj->user_id,
                'name_obj' => $obj->name_obj,
                'phone_obj' => $obj->phone_obj,
                'for_events' => $obj->detailsObj ? $obj->detailsObj->for_events : null,
                'subjs_data' => $subjsData,
            ];
        })->toArray();

        // Формируем итоговый массив для возврата
        return [
            'data' => $transformedData,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'has_more_pages' => $paginated->hasMorePages(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
                'path' => $paginated->path(),
            ],
            'filters' => [
                'for_events' => $forEventsFilter,
                'capacity_to' => $capacityToFilter, // передаём текущий фильтр в ответ
                'per_person' => $perPersonFilter, // передаём фильтр по цене в ответ
                'features' => $featuresFilter, // передаём фильтр по features в ответ
            ],
            'meta' => [
                'query_params' => $request->all(),
                'timestamp' => now()->toDateTimeString(),
            ]
        ];
    }

}