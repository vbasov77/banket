<?php


namespace App\Repositories;


use App\Models\City;
use App\Models\District;
use App\Models\Obj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        // Получаем фильтры из сессии
        $selectedFilters = session('selected_filters', []);
        $forEventsFilter = $selectedFilters['for_events'] ?? null;
        $capacityToFilter = $selectedFilters['capacity_to'] ?? null;
        $perPersonFilter = $selectedFilters['per_person'] ?? null;
        $featuresFilter = $selectedFilters['features'] ?? null;

        // --- НОВЫЙ БЛОК: Получение параметров для сортировки по районам ---
        // 1. Получаем ID города из сессии (название города)
        $userCityName = session('user_city');
        // Находим ID города по названию (кэшируем, чтобы не грузить БД каждый раз)
        $cityId = Cache::remember('city_id_' . $userCityName, 3600, function () use ($userCityName) {
            return City::where('name', $userCityName)->value('id');
        });

        // 2. Получаем выбранные районы из GET-запроса (массив ID)
        // Ожидаем формат: ?districts[]=1&districts[]=5
        $districtIds = $request->input('district');

        // Валидация: если переданы районы, проверяем, что они относятся к текущему городу
        if (!empty($districtIds) && is_array($districtIds)) {
            // Фильтруем только районы, которые принадлежат городу пользователя
            $validDistrictIds = District::where('city_id', $cityId)
                ->whereIn('name', $districtIds)
                ->pluck('id')
                ->values() // Пересобираем ключи массива для правильного использования в SQL
                ->toArray();

            // Если переданных районов нет в этом городе, очищаем фильтр (или можно оставить пустым, сортировка не сработает)
            $districtIds = $validDistrictIds;
        } else {
            $districtIds = [];
        }
        // --------------------------------------------------------------

        // Дополнительно проверяем фильтры в текущем GET-запросе
        if ($request->has('for_events')) $forEventsFilter = $request->input('for_events');
        if ($request->has('capacity_to')) {
            $capacityToFilter = $request->input('capacity_to');
            session(['selected_filters.capacity_to' => $capacityToFilter]);
        }
        if ($request->has('per_person')) {
            $perPersonFilter = $request->input('per_person');
            session(['selected_filters.per_person' => $perPersonFilter]);
        }
        if ($request->has('features')) {
            $featuresFilter = $request->input('features');
            session(['selected_filters.features' => $featuresFilter]);
        }

        $query = Obj::with([
            'detailsObj' => function ($q) {
                $q->select('id', 'obj_id', 'for_events', 'kitchen', 'service',
                    'alcohol', 'more', 'payment_methods', 'text_obj');
            },
            'subjs' => function ($query) use ($cityId) {
                // Добавляем select для district, чтобы потом можно было использовать его (опционально)
                $query->select(
                    'id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_to', 'site_type', 'features', 'text_subj'
                );

                // Опционально: жесткий фильтр по городу на уровне запроса, если нужно ускорить
                // if ($cityId) {
                //     $query->whereHas('addressObj', function($q) use ($cityId) {
                //         $q->where('city', $cityId);
                //     });
                // }
            }
        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj');

        // Применяем фильтры
        if (!is_null($forEventsFilter)) {
            $query->whereHas('detailsObj', function ($q) use ($forEventsFilter) {
                $q->whereJsonContains('for_events', $forEventsFilter);
            });
        }

        if (!is_null($capacityToFilter) && is_numeric($capacityToFilter)) {
            $query->whereHas('subjs', function ($q) use ($capacityToFilter) {
                $q->where('capacity_to', '>=', $capacityToFilter);
            });
        }

        if (!is_null($perPersonFilter) && is_numeric($perPersonFilter)) {
            $query->whereHas('subjs', function ($q) use ($perPersonFilter) {
                $q->where('per_person', '<=', $perPersonFilter);
            });
        }

        if (!is_null($featuresFilter) && is_array($featuresFilter) && !empty($featuresFilter)) {
            $query->whereHas('subjs', function ($q) use ($featuresFilter) {
                $q->where(function ($subQuery) use ($featuresFilter) {
                    foreach ($featuresFilter as $feature) {
                        $subQuery->orWhereJsonContains('features', $feature);
                    }
                });
            });
        }

        // --- ЛОГИКА СОРТИРОВКИ ПО РАЙОНАМ (NATIVE SQL) ---
        if (!empty($districtIds)) {
            // 1. Формируем строку для CASE WHEN
            $caseWhenParts = [];
            foreach ($districtIds as $index => $id) {
                $caseWhenParts[] = "WHEN ga.district_id = {$id} THEN " . ($index + 1);
            }
            $caseSql = "CASE " . implode(" ", $caseWhenParts) . " ELSE 999999 END";

            // 2. Делаем INNER JOIN вместо LEFT JOIN.
            // INNER JOIN гарантирует, что в результат попадут ТОЛЬКО те объекты,
            // у которых есть запись в таблице group_address_objs с выбранным районом.
            $query->join('subjs as s', 'objs.id', '=', 's.obj_id')
                ->join('group_address_objs as ga', 'objs.id', '=', 'ga.obj_id');

            // 3. Добавляем фильтр WHERE, чтобы исключить объекты, у которых district не в списке
            // Это дублирует логику INNER JOIN, но делает её явной и безопасной на случай,
            // если в БД есть адреса с district_id = 0 или NULL.
            $query->whereIn('ga.district_id', $districtIds);

            // 4. Группируем по ID объекта, чтобы убрать дубликаты (так как один объект может иметь несколько залов/адресов)
            $query->groupBy('objs.id');

            // 5. Добавляем сортировку через агрегатную функцию MIN()
            $query->orderByRaw("MIN({$caseSql}) ASC");

            // 6. Добавляем вторичную сортировку по ID объекта
            $query->orderBy('objs.id', 'ASC');
        } else {
            // Если районы не выбраны, сортируем по умолчанию
            $query->orderBy('objs.id', 'desc');
        }
        // ----------------------------------------------------

        // Пагинация
        $paginated = $query->paginate(6)->withQueryString();

        // Трансформация данных
        $transformedData = $paginated->getCollection()->map(function ($obj) {
            $subjsData = $obj->subjs->map(function ($subj) {
                $subj->load([
                    'imgSubjFirst:subj_id,path',
                    'imgSubjs' => function ($q) {
                        $q->select('subj_id', 'path')->orderBy('position')->take(5);
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
                'details_obj' => $obj->detailsObj->toArray(),
            ];
        })->toArray();

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
                'capacity_to' => $capacityToFilter,
                'per_person' => $perPersonFilter,
                'features' => $featuresFilter,
                'districts' => $districtIds, // Возвращаем выбранные районы в ответ
            ],
            'meta' => [
                'query_params' => $request->all(),
                'timestamp' => now()->toDateTimeString(),
            ]
        ];
    }
}



