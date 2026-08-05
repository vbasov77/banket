<?php


namespace App\Repositories;


use App\Models\City;
use App\Models\District;
use App\Models\MetroStation;
use App\Models\Obj;
use App\Models\Subj;
use App\Models\Zags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchRepository extends Repository
{
    public function searchResults(Request $request)
    {
        $selectedFilters = session('selected_filters', []);
        $forEventsFilter = $selectedFilters['for_events'] ?? null;
        $capacityToFilter = $selectedFilters['capacity_to'] ?? null;
        $perPersonFilter = $selectedFilters['per_person'] ?? null;
        $featuresFilter = $selectedFilters['features'] ?? null;
        $districtFilter = $selectedFilters['district'] ?? null;
        $nearMetroId = (int)($selectedFilters['near_metro_id'] ?? null);
        $nearZagsId = (int)($selectedFilters['near_zags_id'] ?? null);

        // Город
        $userCityName = session('user_city');
        $cityId = Cache::remember('city_id_' . $userCityName, 3600, function () use ($userCityName) {
            return City::where('name', $userCityName)->value('id');
        });

        $districtIds = [];
        if ($cityId) {
            if (!empty($districtFilter) && count($districtFilter) > 0) {
                $districtIds = $this->getDistrictIds($districtFilter, $cityId);

                if (!is_array($districtIds)) {
                    $districtIds = [$districtIds];
                }
                $districtIds = array_filter($districtIds, 'is_numeric');

                if (!empty($districtIds)) {
                    // Оставляем только те районы, которые реально принадлежат городу
                    $validDistrictIds = District::where('city_id', $cityId)
                        ->whereIn('id', $districtIds)
                        ->pluck('id')
                        ->toArray();
                    $districtIds = $validDistrictIds;
                }
            }
        }

        // Сохраняем фильтры в сессию
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
        if ($request->has('district')) {
            // Сохраняем именно тот массив, который прошёл валидацию выше
            session(['selected_filters.district' => $districtIds]);
        }
        if ($request->has('near_metro_id')) {
            $nearMetroId = $request->input('near_metro_id');
            session(['selected_filters.near_metro_id' => $nearMetroId]);
        }
        if ($request->has('near_zags_id')) {
            $nearZagsId = $request->input('near_zags_id');
            session(['selected_filters.near_zags_id' => $nearZagsId]);
        }


        // Подготовка для CASE WHEN district_id IN (...)
        $districtIdsForCase = empty($districtIds) ? [0] : $districtIds;
        $placeholders = implode(',', array_fill(0, count($districtIdsForCase), '?'));

        $query = Obj::with([
            'detailsObj' => fn($q) => $q->select(
                'id', 'obj_id', 'for_events', 'kitchen', 'service',
                'alcohol', 'more', 'payment_methods', 'description', 'text_obj'
            ),
            // Добавляем группы адресов — они нужны для карты и новой логики сортировки
            'groupAddressObjs' => fn($q) => $q
                ->select('id', 'obj_id', 'address', 'latitude', 'longitude', 'district_id')
                ->with('district:id,name'),
            'subjs' => function ($query) use ($districtIds, $districtIdsForCase, $placeholders) {
                $query->select(
                    'subjs.id', 'subjs.obj_id', 'subjs.name_subj', 'subjs.minimum_cost',
                    'subjs.per_person', 'subjs.capacity_to', 'subjs.site_type',
                    'subjs.features', 'subjs.text_subj'
                )
                    ->leftJoin('address_subjs', 'subjs.id', '=', 'address_subjs.subj_id')
                    ->selectRaw(
                        "(CASE WHEN address_subjs.district_id IN ($placeholders) THEN 0 ELSE 1 END) as district_priority",
                        $districtIdsForCase
                    )
                    ->with([
                        'addressSubj' => fn($q) => $q->select('id', 'subj_id', 'district_id')
                            ->with(['district' => fn($d) => $d->select('id', 'name')]),
                        // subjNearMetro больше не нужен для логики, но можно оставить, если используется в UI
                        'subjNearMetro' => fn($q) => $q
                            ->orderBy('rank', 'asc')
                            ->with('metroStation:id,name'),
                        'primaryImg' => fn($q) => $q->select('id', 'subj_id', 'small_img'),
                        'imgSubjs',
                    ])
                    ->orderBy('district_priority', 'ASC')
                    ->orderBy('subjs.id', 'ASC');
            },
        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj')
            ->orderBy('id', 'desc');

        // Фильтры
        if (!is_null($forEventsFilter)) {
            $query->whereHas('detailsObj', fn($q) => $q->whereJsonContains('for_events', $forEventsFilter));
        }

        if (!is_null($capacityToFilter) && is_numeric($capacityToFilter)) {
            $query->whereHas('subjs', fn($q) => $q->where('capacity_to', '>=', $capacityToFilter));
        }

        if (!is_null($perPersonFilter) && is_numeric($perPersonFilter)) {
            $query->whereHas('subjs', fn($q) => $q->where('per_person', '<=', $perPersonFilter));
        }

        if (!is_null($featuresFilter) && is_array($featuresFilter) && !empty($featuresFilter)) {
            $query->whereHas('subjs', function ($q) use ($featuresFilter) {
                $q->where(function ($sub) use ($featuresFilter) {
                    foreach ($featuresFilter as $f) {
                        $sub->orWhereJsonContains('features', $f);
                    }
                });
            });
        }

        // --- ГЛАВНОЕ: фильтр по районам ---
        if (!empty($districtIds) && $cityId) {
            // Фильтр: у объекта должен быть хотя бы один субъект в выбранных районах
            $query->whereHas('subjs.addressSubj.district', fn($q) => $q->whereIn('id', $districtIds));

            // Сортировка: сначала объекты, у которых есть субъекты в выбранных районах,
            // затем внутри них — по приоритету района (по порядку, в каком пользователь выбрал)
            $subquery = Subj::select('obj_id')
                ->join('address_subjs', 'subjs.id', '=', 'address_subjs.subj_id')
                ->join('districts', 'address_subjs.district_id', '=', 'districts.id')
                ->whereIn('districts.id', $districtIds)
                ->selectRaw('MIN(FIELD(districts.id,' . implode(',', $districtIds) . ')) as priority')
                ->groupBy('obj_id');

            $query->joinSub($subquery, 'subj_priorities', fn($join) => $join->on('objs.id', '=', 'subj_priorities.obj_id'))
                ->orderBy('subj_priorities.priority', 'ASC')
                ->orderBy('id', 'ASC');
        } else {
            // Без фильтрации по районам — обычная сортировка
            $query->orderBy('id', 'DESC');
        }

        // сортировка по ближайшей группе адресов к станции ---
        if ($nearMetroId) {
            // 1. Получаем координаты выбранной станции метро
            $station = MetroStation::findOrFail($nearMetroId);
            $stationLat = $station->latitude;
            $stationLon = $station->longitude;

            // 2. Подзапрос: минимальное расстояние в метрах до станции по всем группам объекта
            $subqueryMetro = DB::table('group_address_objs')
                ->select('obj_id')
                ->selectRaw(
                    'MIN(ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?))) AS min_distance',
                    [$stationLon, $stationLat]  // порядок: lon, lat
                )
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->groupBy('obj_id');

            // 3. Присоединяем подзапрос
            $query->joinSub($subqueryMetro, 'metro_dist', function ($join) {
                $join->on('objs.id', '=', 'metro_dist.obj_id');
            });

            // 4. Сбрасываем любые предыдущие сортировки, чтобы не было конфликта
            $query->reorder(); // удаляет все предыдущие orderBy

            // 5. Сортируем строго по расстоянию, потом по ID
            $query->orderBy('metro_dist.min_distance', 'ASC')
                ->orderBy('objs.id', 'ASC');
        }
        if ($nearZagsId) {
            // 1. Получаем координаты выбранного ЗАГСа
            $zags = Zags::findOrFail($nearZagsId);
            $zagsLat = $zags->latitude;
            $zagsLon = $zags->longitude;

            // Если у ЗАГСа нет координат — не делаем сортировку (или можно выбросить ошибку)
            if (!$zagsLat || !$zagsLon) {
                throw new \InvalidArgumentException('У выбранного ЗАГСа нет координат для расчёта расстояния');
            }

            // 2. Подзапрос: для каждого объекта считаем минимальное расстояние до ЗАГСа
            // по всем его группам адресов (group_address_objs)
            $subqueryZags = DB::table('group_address_objs')
                ->select('obj_id')
                ->selectRaw(
                    'MIN(ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?))) AS min_distance',
                    [$zagsLon, $zagsLat]  // порядок: lon, lat
                )
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->groupBy('obj_id');

            // 3. Присоединяем подзапрос к основному запросу
            $query->joinSub($subqueryZags, 'zags_dist', function ($join) {
                $join->on('objs.id', '=', 'zags_dist.obj_id');
            });

            // 4. Сбрасываем предыдущие сортировки, чтобы не было конфликта
            $query->reorder();

            // 5. Сортируем строго по расстоянию до ЗАГСа, потом по ID
            $query->orderBy('zags_dist.min_distance', 'ASC')
                ->orderBy('objs.id', 'ASC');
        }



        $paginated = $query->paginate(6)->withQueryString();

        // Трансформация
        $transformedData = $paginated->getCollection()->map(function ($obj) {
            if ($obj->subjs) {
                $obj->subjs->transform(function ($subj) {
                    $metroStations = [];
                    if ($subj->subjNearMetro->isNotEmpty()) {
                        foreach ($subj->subjNearMetro as $metro) {
                            if ($metro->metroStation) {
                                $metroStations[] = [
                                    'station_name' => $metro->metroStation->name,
                                    'distance_km' => $metro->distance_km,
                                    'rank' => $metro->rank,
                                ];
                            }
                        }
                    }

                    $districtName = $subj->addressSubj?->district?->name;

                    return [
                        'id' => $subj->id,
                        'name_subj' => $subj->name_subj,
                        'minimum_cost' => $subj->minimum_cost,
                        'per_person' => $subj->per_person,
                        'capacity_to' => $subj->capacity_to,
                        'site_type' => $subj->site_type,
                        'features' => $subj->features,
                        'text_subj' => $subj->text_subj,
                        'path' => $subj->primaryImg?->small_img,
                        'image_paths' => $subj->imgSubjs
                            ? $subj->imgSubjs->take(5)->pluck('small_img')->toArray()
                            : [],
                        'district_name' => $districtName,
                        'metro_stations' => $metroStations,
                    ];
                });
            }

            // Для карты: оставляем группы адресов (в них есть координаты)
            if ($obj->groupAddressObjs) {
                $obj->groupAddressObjs->transform(fn($g) => [
                    'id' => $g->id,
                    'address' => $g->address,
                    'latitude' => $g->latitude,
                    'longitude' => $g->longitude,
                    'district_name' => $g->district?->name ?? null,
                ]);
            }

            return [
                'obj_id' => $obj->id,
                'user_id' => $obj->user_id,
                'name_obj' => $obj->name_obj,
                'phone_obj' => $obj->phone_obj,
                'subjs_data' => $obj->subjs?->toArray() ?? [],
                'details_obj' => $obj->detailsObj?->toArray() ?? [],
                'groups_addresses' => $obj->groupAddressObjs?->toArray() ?? [], // переименовано для ясности
                'districts_names' => $obj->groupAddressObjs
                    ? $obj->groupAddressObjs->pluck('district_name')->toArray()
                    : [],
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

    public function getFiltersDataByCity(int $cityId): array
    {
        // ЗАГСы
        $zags = Zags::where('city_id', $cityId)
            ->select('id', 'name')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        // Районы
        $districts = District::where('city_id', $cityId)
            ->select('id', 'name')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        // Станции метро
        $metros = MetroStation::where('city_id', $cityId)
            ->select('id', 'name', 'latitude', 'longitude')  // убрали line_name
            ->orderBy('name')
            ->get()
            ->map(fn ($m) => [
                'id'      => $m->id,
                'name'    => $m->name,
                'display' => $m->name,                        // без линии
                'lat'     => (float)$m->latitude,
                'lng'     => (float)$m->longitude,
            ])
            ->values()
            ->toArray();

        return [
            'zags'      => $zags,
            'districts' => $districts,
            'metros'    => $metros,
        ];
    }

    public function getReadableFilters(array $rawFilters): array
    {
        $result = [];

        // 1. Район (district может быть int или array)
        if (!empty($rawFilters['district'])) {
            $ids = (array)$rawFilters['district'];
            if (!empty($ids) && count($ids) > 0) {
                $result['district'] = "📍 Район: " . implode(', ', $ids);
            }
        }

        // 2. Метро (near_metro_id)
        if (!empty($rawFilters['near_metro_id'])) {
            $ids = (array)$rawFilters['near_metro_id'];
            $names = MetroStation::whereIn('id', $ids)->pluck('name')->toArray();
            if (!empty($names)) {
                $result['metro'] = "🚇 Метро: " . implode(', ', $names);
            }
        }

        // 3. Вместимость до (capacity_to)
        if (!empty($rawFilters['capacity_to'])) {
            $result['capacity'] = "👥 Вместимость до: {$rawFilters['capacity_to']} чел.";
        }

        // 4. На человека до (per_person)
        if (!empty($rawFilters['per_person'])) {
            $formatted = number_format((int)$rawFilters['per_person'], 0, '', ' ');
            $result['price'] = "💰 На человека до: {$formatted} ₽";
        }

        // 5. Особенности (features)
        if (!empty($rawFilters['features'])) {
            $features = (array)$rawFilters['features'];
            // Показываем первые 3, остальное через многоточие
            $shown = array_slice($features, 0, 3);
            $suffix = count($features) > 3 ? '...' : '';
            $result['features'] = "✨ Особенности: " . implode(', ', $shown) . $suffix;
        }

        // 6. Тип события (for_events)
        if (!empty($rawFilters['for_events'])) {
            $result['event'] = "🎉 Событие: {$rawFilters['for_events']}";
        }

        return $result;
    }

}



