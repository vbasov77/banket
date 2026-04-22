<?php


namespace App\Repositories;


use App\Models\City;
use App\Models\District;
use App\Models\Obj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        if (!$cityId) {
            $districtIds = [];
        } else {
            // 2. Получаем выбранные районы из GET-запроса (массив ID)
            $districtIds = $this->getDistrictIds($request->input('district', []), $cityId);

            if (!is_array($districtIds)) {
                $districtIds = [$districtIds];
            }
            $districtIds = array_filter($districtIds, 'is_numeric');

            // Валидация: если переданы районы, проверяем, что они относятся к текущему городу
            if (!empty($districtIds)) {
                // Фильтруем только районы, которые принадлежат городу пользователя
                $validDistrictIds = District::where('city_id', $cityId)
                    ->whereIn('id', $districtIds)
                    ->pluck('id')
                    ->toArray();
                $districtIds = $validDistrictIds;

            } else {
                $districtIds = [];
            }


        }
        // --------------------------------------------------------------

        // Дополнительно проверяем фильтры в текущем GET-запросе
        if ($request->has('for_events')) {
            $forEventsFilter = $request->input('for_events');
        }
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
                $query->select(
                    'id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_to', 'site_type', 'features', 'text_subj'
                )->with(['addressSubj' => function ($q) {
                    $q->select('id', 'subj_id', 'district_id')
                        ->with(['district' => function ($d) {
                            $d->select('id', 'name');
                        }]);
                }]);

            },
            'subjs.imgSubjFirst',
            'subjs.imgSubjs' => function ($q) {
                $q->select('subj_id', 'path')->orderBy('position')->take(5);
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

        // --- ЛОГИКА СОРТИРОВКИ ПО РАЙОНАМ ---
        if (!empty($districtIds) && $cityId) {
            $caseBindings = [];
            $caseParts = [];

            foreach ($districtIds as $index => $id) {
                $caseParts[] = "WHEN ga.district_id = ? THEN " . ($index + 1);
                $caseBindings[] = $id;
            }

            $caseSql = "CASE " . implode(" ", $caseParts) . " ELSE 999999 END";

            $query->join('subjs as s', 'objs.id', '=', 's.obj_id')
                ->join('group_address_objs as ga', 'objs.id', '=', 'ga.obj_id')
                ->whereIn('ga.district_id', $districtIds)
                ->groupBy('objs.id')
                ->orderByRaw("MIN({$caseSql}) ASC", $caseBindings)
                ->orderBy('objs.id', 'ASC');
        } else {
            $query->orderBy('objs.id', 'desc');
        }
        // ----------------------------------------------------

        // Пагинация
        $paginated = $query->paginate(6)->withQueryString();

        // Трансформация данных
        $transformedData = $paginated->getCollection()->map(function ($obj) {
            $subjsData = $obj->subjs->map(function ($subj) {

                // Извлекаем название района через addressSubj → district
                $districtName = $subj->addressSubj && $subj->addressSubj->district
                    ? $subj->addressSubj->district->name
                    : null;
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
                    'district_name' => $districtName
                ];
            })->toArray();

            return [
                'obj_id' => $obj->id,
                'user_id' => $obj->user_id,
                'name_obj' => $obj->name_obj,
                'phone_obj' => $obj->phone_obj,
                'for_events' => $obj->detailsObj ? $obj->detailsObj->for_events : null,
                'subjs_data' => $subjsData,
                'details_obj' => $obj->detailsObj ? $obj->detailsObj->toArray() : null,
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
                'city_id' => $cityId, // Добавляем ID города для отладки
                'user_city' => $userCityName, // Название города пользователя
                'applied_district_filters' => !empty($districtIds), // Флаг применения фильтра по районам
            ]
        ];
    }

    private function getDistrictIds(array $districtInput, int $cityId): array
    {
        $resultIds = [];

        foreach ($districtInput as $item) {
            if (is_numeric($item)) {
                // Если уже ID — просто добавляем
                $resultIds[] = (int)$item;
            } elseif (is_string($item) && !empty(trim($item))) {
                // Ищем ID по названию района
                $districtId = District::where('city_id', $cityId)
                    ->where('name', trim($item))
                    ->value('id');

                if ($districtId) {
                    $resultIds[] = $districtId;
                }
            }
        }

        return array_unique($resultIds); // Убираем дубликаты
    }

}



