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

class ObjRepository extends Repository
{

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return Obj::with([
            'detailsObj', // если нужны поля — дополните select
            'subjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_to', 'site_type', 'text_subj', 'published', 'id')
                    ->with(['imgSubjFirst:subj_id,path']); // загружаем первое img_subj для каждого subj
            }
        ])->where('subjs.id', $id)
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj') // ограничиваем поля основной таблицы
            ->get()
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
                            'text_subj' => $subj->text_subj,
                            'published' => $subj->published,
//                            'path' => $subj->imgSubjFirst ? $subj->imgSubjFirst->path : null,
                            'image_paths' => $subj->imgSubjs->pluck('path')->toArray()
                        ];
                    })->toArray(),

                ];
            });
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
                    'capacity_to', 'site_type', 'text_subj', 'published', 'id')
                    ->with(['imgSubjFirst:subj_id,path']); // загружаем первое img_subj для каждого subj
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
                            'text_subj' => $subj->text_subj,
                            'published' => $subj->published,
                            'path' => $subj->imgSubjFirst ? $subj->imgSubjFirst->path : null,
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
    public function findObjsWithDetails(Request $request): LengthAwarePaginator
    {
        $userCityService = new UserCityService(new UserCityRepository());
        $cityId = session('city_id');
        if (!$cityId) {
            $userCityService->checkSessionUserCity($request);
            $cityId = session('city_id');
        }

        // Фильтрация и пагинация на уровне базы данных
        $paginated = Obj::with([
            'detailsObj' => function ($q) {
                $q->select('id', 'obj_id', 'for_events', 'kitchen', 'service',
                    'alcohol', 'more', 'payment_methods', 'text_obj');
            },
            'subjs' => function ($query) use ($cityId) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person', 'capacity_to', 'site_type', 'features', 'text_subj')
                    ->whereHas('addressSubj', function ($q) use ($cityId) {
                        $q->where('city_id', $cityId);
                    })
                    ->with(['addressSubj' => function ($q) use ($cityId) {
                        $q->select('id', 'subj_id', 'district_id')
                            ->where('city_id', $cityId)
                            ->with(['district' => function ($d) {
                                $d->select('id', 'name');
                            }]);
                    }]);
            },
            'groupAddressObjs' => function ($v) {
                $v->select('id', 'district_id', 'obj_id');
            },
        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj')
            ->whereHas('subjs.addressSubj', function ($query) use ($cityId) {
                $query->where('city_id', $cityId);
            })
            ->paginate(7);

        if ($paginated->isEmpty()) {
            return $paginated; // Возвращаем оригинальный пагинатор, если данных нет
        }

        // Трансформация данных
        $transformedData = $paginated->getCollection()->map(function ($obj) {
            if ($obj->subjs) {
                $obj->subjs->transform(function ($subj) {
                    $subj->load([
                        'imgSubjFirst:subj_id,path',
                        'imgSubjs' => function ($q) {
                            $q->select('subj_id', 'path')
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
                        'path' => $subj->imgSubjFirst && $subj->imgSubjFirst->path ? $subj->imgSubjFirst->path : null,
                        'image_paths' => $subj->imgSubjs ? $subj->imgSubjs->pluck('path')->toArray() : [],
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

// Создаём новый пагинатор с трансформированными данными
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformedData->values(),
            $paginated->total(),
            $paginated->perPage(),
            $paginated->currentPage(),
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        return $paginator;
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
                'subjects' => function ($query) {
                    $query->select([
                        'id', 'obj_id', 'name_subj',
                        'minimum_cost', 'per_person', 'capacity_to', 'site_type', 'text_subj', 'published', 'features'
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
                        ->select(['subj_id', 'path', 'position'])
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