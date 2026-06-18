<?php


namespace App\Repositories;


use App\Models\City;
use App\Models\District;
use App\Models\Obj;
use App\Models\Subj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchRepository extends Repository
{
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
            'subjs' => function ($query) use ($cityId, $districtIds) {
                $query->select(
                    'subjs.id', 'subjs.obj_id', 'subjs.name_subj', 'subjs.minimum_cost',
                    'subjs.per_person', 'subjs.capacity_to', 'subjs.site_type',
                    'subjs.features', 'subjs.text_subj'
                )
                    ->leftJoin('address_subjs', 'subjs.id', '=', 'address_subjs.subj_id')
                    ->selectRaw(
                        '(CASE WHEN address_subjs.district_id IN (' .
                        (empty($districtIds) ? '0' : implode(',', $districtIds)) .
                        ') THEN 0 ELSE 1 END) as district_priority'
                    )
                    ->with([
                        'addressSubj' => function ($q) use ($districtIds) {
                            $q->select('id', 'subj_id', 'district_id')
                                ->with(['district' => function ($d) {
                                    $d->select('id', 'name');
                                }]);
                        },
                        'primaryImg' => function ($q) {
                            $q->select('id', 'subj_id', 'small_img');
                        },
                        'imgSubjs' => function ($q) {
                            $q->select('subj_id', 'small_img')->orderBy('position')->take(5);
                        }
                    ])
                    ->orderBy('district_priority', 'ASC')
                    ->orderBy('subjs.id', 'ASC');
            }

        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj')
        ->orderBy('id', 'desc');



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
            // Фильтруем объекты: только те, у которых есть субъекты в выбранных районах
            $query->whereHas('subjs.addressSubj.district', function ($q) use ($districtIds) {
                $q->whereIn('id', $districtIds);
            });

            // Сортируем по приоритету районов субъектов
            $subquery = Subj::select('obj_id')
                ->join('address_subjs', 'subjs.id', '=', 'address_subjs.subj_id')
                ->join('districts', 'address_subjs.district_id', '=', 'districts.id')
                ->whereIn('districts.id', $districtIds)
                ->selectRaw('MIN(FIELD(districts.id, ' . implode(',', $districtIds) . ')) as priority')
                ->groupBy('obj_id');

            $query->joinSub($subquery, 'subj_priorities', function ($join) {
                $join->on('objs.id', '=', 'subj_priorities.obj_id');
            })->orderBy('subj_priorities.priority', 'ASC');

            $query->orderBy('id', 'ASC');
        } else {
            $query->orderBy('id', 'DESC');
        }


        // ----------------------------------------------------

        // Пагинация
        $paginated = $query->paginate(6)->withQueryString();

        // Трансформация данных
        $transformedData = $paginated->getCollection()->map(function ($obj) {
            if ($obj->subjs) {
                $obj->subjs->transform(function ($subj) {
                    $subj->load([
                        'primaryImg:subj_id,small_img',
                        'imgSubjs' => function ($q) {
                            $q->select('subj_id', 'small_img')
                                ->orderBy('position')
                                ->take(5);
                        }
                    ]);

                    $districtName = null;
                    if ($subj->addressSubj && $subj->addressSubj->district) {
                        $districtName = $subj->addressSubj->district->name;
                    }

                    return [
                        'id' => $subj->id ?? null,
                        'name_subj' => $subj->name_subj ?? null,
                        'minimum_cost' => $subj->minimum_cost ?? null,
                        'per_person' => $subj->per_person ?? null,
                        'capacity_to' => $subj->capacity_to ?? null,
                        'site_type' => $subj->site_type ?? null,
                        'features' => $subj->features ?? null,
                        'text_subj' => $subj->text_subj ?? null,
                        'path' => $subj->primaryImg && $subj->primaryImg->small_img ? $subj->primaryImg->small_img : null,
                        'image_paths' => $subj->imgSubjs ? $subj->imgSubjs->pluck('small_img')->toArray() : [],
                        'district_name' => $districtName
                    ];
                });
            }

            if ($obj->groupAddressObjs) {
                $obj->groupAddressObjs->transform(function ($groupAddressObjs) {
                    $groupAddressObjs->load(['district:id,name']);

                    return [
                        'id' => $groupAddressObjs->district && $groupAddressObjs->district->id ? $groupAddressObjs->district->id : null,
                        'name' => $groupAddressObjs->district && $groupAddressObjs->district->name ? $groupAddressObjs->district->name : null,
                    ];
                });
            }

            return [
                'obj_id' => $obj->id ?? null,
                'user_id' => $obj->user_id ?? null,
                'name_obj' => $obj->name_obj ?? null,
                'phone_obj' => $obj->phone_obj ?? null,
                'subjs_data' => $obj->subjs ? $obj->subjs->toArray() : [],
                'details_obj' => $obj->detailsObj ? $obj->detailsObj->toArray() : [],
                'districts' => $obj->groupAddressObjs ? $obj->groupAddressObjs->toArray() : [],
                'districts_names' => $obj->groupAddressObjs ? $obj->groupAddressObjs->pluck('district.name')->toArray() : [],
            ];
        });


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



