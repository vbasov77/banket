<?php


namespace App\Repositories;


use App\Models\Obj;
use App\Services\UserCityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * @param array $array
     * @param int $id
     * @return void
     */
    public function update(array $array, int $id): void
    {
        DB::table('objs')->where('id', $id)->update($array);
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
        } catch (\Illuminate\Database\QueryException $e) {
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function findObjsWithDetails(Request $request)
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
     * @param int $id
     * @return mixed
     */
    public function findByIdOnlyObj(int $id): mixed
    {
        return Obj::where('id', $id)->first();
    }

}