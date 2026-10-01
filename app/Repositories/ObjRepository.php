<?php


namespace App\Repositories;


use App\Models\Obj;
use App\Services\UserCityService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;


class ObjRepository extends Repository
{

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id): ?array
    {
        $obj = Obj::with([
            'detailsObj' => function ($query) {
                $query->select(
                    'obj_id', 'for_events', 'kitchen', 'service', 'alcohol',
                    'payment_methods', 'service_fee', 'bring_with_you', 'text_obj'
                );
            },
            'features' => function ($query) {
                $query->select(
                    'obj_id', 'banquet_note', 'prepayment', 'textile_package',
                    'textile_colors', 'tables', 'loud_music', 'parking', 'pier',
                    'equipment', 'kids', 'interior', 'location'
                );
            },
            'actions' => function ($query) {
                $query->select('id', 'obj_id', 'actions');
            },
            'allSubjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_to', 'furshet', 'site_type', 'published')
                    ->with([
                        'imgSubjs' => function ($q) {
                            $q->select('id', 'subj_id', 'small_img', 'big_img')
                                ->orderBy('position', 'asc')
                                ->orderBy('id', 'asc');
                        },
                        'addressSubj' => function ($q) {
                            $q->select('subj_id', 'address', 'city_id', 'district_id')
                                ->with('district:id,name');
                        },
                        'subjNearMetro' => function ($q) {
                            $q->select('subj_id', 'metro_station_id', 'distance_km', 'rank')
                                ->with(['metroStation:id,name'])
                                ->orderBy('rank', 'asc');
                        },
                        'favorites' => function ($q) {
                            $q->select('subj_id', 'user_id')
                                ->where('user_id', Auth::id());
                        },
                    ]);
            },
        ])
            ->select('id', 'user_id', 'name_obj', 'phone_obj')
            ->where('id', $id)
            ->first();

        if (!$obj) {
            return null;
        }

        return [
            'obj_id' => $obj->id,
            'user_id' => $obj->user_id,
            'name_obj' => $obj->name_obj,
            'phone_obj' => $obj->phone_obj,

            'details' => $obj->detailsObj ? $obj->detailsObj->toArray() : [],
            'features' => $obj->features ? $obj->features->toArray() : [],
            'actions' => $obj->actions
                ? ($obj->actions instanceof \Illuminate\Support\Collection
                    ? $obj->actions->toArray()
                    : [$obj->actions->toArray()])
                : [],


            'subjs_data' => $obj->allSubjs->map(function ($subj) use ($obj) {
                $images = $subj->imgSubjs;
                $address = $subj->addressSubj;

                return [
                    'subj_id' => $subj->id,
                    'name_subj' => $subj->name_subj,
                    'minimum_cost' => $subj->minimum_cost,
                    'per_person' => $subj->per_person,
                    'capacity_to' => $subj->capacity_to,
                    'furshet' => $subj->furshet,
                    'site_type' => $subj->site_type,
                    'published' => (bool)$subj->published,
                    'is_favorite' => $subj->favorites->isNotEmpty(),
                    'image_paths' => $images->pluck('small_img')->toArray(),
                    'big_image_paths' => $images->pluck('big_img')->toArray(),

                    'address' => $address?->address,
                    'city_id' => $address?->city_id,
                    'district_id' => $address?->district_id,
                    'district_name' => $address?->district?->name,

                    'nearest_metros' => $subj->subjNearMetro->map(function ($metro) {
                        return [
                            'metro_station_id' => $metro->metro_station_id,
                            'distance_km' => $metro->distance_km,
                            'rank' => $metro->rank,
                            'station_name' => $metro->metroStation?->name,
                        ];
                    })->toArray(),

                    'related_subjs' => $obj->allSubjs
                        ->where('id', '!=', $subj->id)
                        ->where('published', 1)
                        ->map(function ($related) {
                            return [
                                'subj_id' => $related->id,
                                'name_subj' => $related->name_subj,
                                'image_path' => $related->imgSubjs->first()?->small_img,
                                'capacity_to' => $related->capacity_to,
                                'minimum_cost' => $related->minimum_cost,
                            ];
                        })->values()->toArray(),
                ];
            })->toArray(),
        ];
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function findByUserId(int $userId)
    {
        return Obj::where('user_id', $userId)->get();
    }


    /**
     * Обновить запись объекта в базе данных
     *
     * @param array $data
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function update(array $data, int $id): void
    {
        try {
            // Проверяем, существует ли объект перед обновлением
            $exists = DB::table('objs')->where('id', $id)->exists();
            if (!$exists) {
                Log::channel('error_file')->error(
                    'Attempt to update non-existent object: ' . $id
                );
                throw new \Exception('Object not found for update');
            }

            DB::table('objs')->where('id', $id)->update($data);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in ObjRepository@update: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $id, 'sql' => $e->getSql(), 'data' => $data]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in ObjRepository@update: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $id, 'data' => $data]
            );
            throw $e;
        }
    }

    public function findIdObjByUserId()
    {
        return Obj::where('user_id', Auth::user()->id)->value('id');
    }

    /**
     * @return Obj|null
     */
    public function findObjByUserId(): ?Obj
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                Log::channel('error_file')->warning('Unauthenticated user attempt in ObjRepository@findObjByUserId');
                return null;
            }

            return Obj::where('user_id', $userId)->first();
        } catch (QueryException $e) {
            Log::channel('error_file')->error('Database error in ObjRepository@findObjByUserId', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $userId ?? 'unknown',
            ]);
            throw $e;
        }
    }


    public function findMyObj(int $userId)
    {
        return Obj::with([
            'detailsObj', // если нужны поля — дополните select
            'subjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_to', 'site_type', 'published', 'id')
                    ->with(['primaryImg:subj_id,small_img']); // загружаем первое img_subj для каждого subj
            }
        ])->where('objs.user_id', $userId)
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj') // ограничиваем поля основной таблицы
            ->get() // пагинация: 10 объектов на страницу
            ->map(function ($obj) {
                return [
                    'obj_id' => $obj->id,
                    'user_id' => $obj->user_id,
                    'name_obj' => $obj->name_obj,
                    'phone_obj' => $obj->phone_obj,
                    'subjs_data' => $obj->subjs->map(function ($subj) {
                        return [
                            'id' => $subj->id,
                            'name_subj' => $subj->name_subj,
                            'minimum_cost' => $subj->minimum_cost,
                            'per_person' => $subj->per_person,
                            'capacity_to' => $subj->capacity_to,
                            'site_type' => $subj->site_type,
                            'published' => $subj->published,
                            'path' => $subj->primaryImg ? $subj->primaryImg->small_img : null,
                            'image_paths' => $subj->imgSubjs->pluck('path')->toArray()
                        ];
                    })->toArray(),

                ];
            });
    }


    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function findObjsWithDetails(Request $request, ?string $searchQuery = null): LengthAwarePaginator
    {
        $userCityService = new UserCityService(new UserCityRepository());
        $cityId = (int)session('city_id');

        if (!$cityId) {
            $userCityService->checkSessionUserCity($request);
            $cityId = (int)session('city_id');
            if (!$cityId) {
                // fallback: пустой пагинатор
                return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 7);
            }
        }

        $builder = Obj::with([
            'detailsObj' => fn($q) => $q->select(
                'id', 'obj_id', 'for_events', 'kitchen', 'service',
                'alcohol', 'payment_methods', 'description', 'text_obj'
            ),
            'subjs' => fn($query) => $query
                ->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person', 'capacity_to', 'site_type')
                ->whereHas('addressSubj', fn($q) => $q->where('city_id', $cityId))
                ->with([
                    'addressSubj' => fn($q) => $q
                        ->select('id', 'subj_id', 'district_id', 'address')
                        ->where('city_id', $cityId)
                        ->with(['district' => fn($d) => $d->select('id', 'name')]),
                    'subjNearMetro' => fn($q) => $q
                        ->orderBy('rank', 'asc')
                        ->with('metroStation:id,name'),
                    'imgSubjs',
                ]),
            'groupAddressObjs' => fn($v) => $v->select('id', 'district_id', 'obj_id'),
        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj')
            ->whereHas('subjs.addressSubj', fn($q) => $q->where('city_id', $cityId));

        // 🔍 Поиск по названию объекта
        if ($searchQuery && trim($searchQuery) !== '') {
            $builder->where('name_obj', 'LIKE', '%' . trim($searchQuery) . '%');
        }

        $paginated = $builder->paginate(7);

        if ($paginated->isEmpty()) {
            return $paginated;
        }

        $transformedData = $paginated->getCollection()->map(function ($obj) {
            // Трансформация subjs
            $subjsData = [];
            if ($obj->subjs) {
                $subjsData = $obj->subjs->map(function ($subj) {
                    $districtName = $subj->addressSubj?->district?->name;

                    $metroStations = [];
                    if ($subj->subjNearMetro) {
                        foreach ($subj->subjNearMetro as $metro) {
                            if ($metro->metroStation) {
                                $metroStations[] = [
                                    'station_name' => $metro->metroStation->name,
                                    'distance_km' => $metro->distance_km,
                                ];
                            }
                        }
                    }

                    return [
                        'id' => $subj->id,
                        'name_subj' => $subj->name_subj,
                        'minimum_cost' => $subj->minimum_cost,
                        'per_person' => $subj->per_person,
                        'capacity_to' => $subj->capacity_to,
                        'site_type' => $subj->site_type,
                        'address' => $subj->addressSubj->address,
                        'image_paths' => $subj->imgSubjs
                            ? $subj->imgSubjs->take(5)->pluck('small_img')->toArray()
                            : [],
                        'district_name' => $districtName,
                        'metro_stations' => $metroStations,
                    ];
                });
            }

            // Трансформация groupAddressObjs
            $districtsData = [];
            if ($obj->groupAddressObjs) {
                $districtsData = $obj->groupAddressObjs->map(function ($group) {
                    return [
                        'id' => $group->district?->id,
                        'name' => $group->district?->name,
                    ];
                });
            }

            return [
                'obj_id' => $obj->id,
                'user_id' => $obj->user_id,
                'name_obj' => $obj->name_obj,
                'phone_obj' => $obj->phone_obj,
                'subjs_data' => $subjsData->toArray(),
                'details_obj' => $obj->detailsObj?->toArray(),
                'districts' => $districtsData->toArray(),
                'districts_names' => $districtsData->pluck('name')->toArray(),
            ];
        });

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $transformedData->values(),
            $paginated->total(),
            $paginated->perPage(),
            $paginated->currentPage(),
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );
    }

    /**
     * Найти объект по ID из базы данных
     *
     * @param int $id
     * @return \App\Models\Obj|null
     */
    public function findByIdOnlyObj(int $id)
    {
        try {
            return Obj::where('id', $id)->first();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in ObjRepository@findByIdOnlyObj: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $id, 'sql' => $e->getSql()]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in ObjRepository@findByIdOnlyObj: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $id]
            );
            throw $e;
        }
    }

    /**
     * @param int $objId
     * @return array|null
     * @throws \Exception
     */
    public function findMySubjs(int $objId): ?array
    {
        try {
            // 1. Получаем obj и subjs БЕЗ фото
            $obj = Obj::with([
                'detailsObj:*',
                'features:*',
                'subjects' => function ($query) {
                    $query->select([
                        'id', 'obj_id', 'name_subj',
                        'minimum_cost', 'per_person', 'capacity_to', 'site_type', 'published'
                    ]);
                },
                'user:*',
                'imgObj:*'
            ])
                ->where('id', $objId)
                ->select(['id', 'user_id', 'name_obj', 'phone_obj'])
                ->first();

            if (!$obj) {
                Log::channel('error_file')->error(
                    'Object not found in repository@findMySubjs',
                    [
                        'obj_id' => $objId
                    ]
                );
                return null;
            }

            // 2. Вручную загружаем primaryImg для КАЖДОГО subj
            foreach ($obj->subjects as $subj) {
                try {
                    $subj->primaryImg = $subj->primaryImg()
                        ->select(['subj_id', 'small_img', 'position'])
                        ->first();
                } catch (\Exception $imgError) {
                    Log::channel('error_file')->error(
                        'Error loading primary image for subject: ' . $subj->id,
                        [
                            'trace' => $imgError->getTrace(),
                            'subj_id' => $subj->id
                        ]
                    );
                    // Продолжаем обработку остальных субъектов
                }
            }

            return $obj->toArray();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in repository@findMySubjs: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'obj_id' => $objId,
                    'sql' => $e->getSql()
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in repository@findMySubjs: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'obj_id' => $objId
                ]
            );
            throw $e;
        }
    }


}