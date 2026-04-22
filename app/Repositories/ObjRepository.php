<?php


namespace App\Repositories;


use App\Models\Obj;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObjRepository extends Repository
{
    public function __construct() {} // Пустой конструктор

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


    public function findObjsWithDetails()
    {
        $paginated = Obj::with([
            'detailsObj' => function ($q) {
                $q->select('id', 'obj_id', 'for_events', 'kitchen', 'service',
                    'alcohol', 'more', 'payment_methods', 'text_obj');
            },
            'subjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person', 'capacity_to', 'site_type', 'features', 'text_subj')
                    ->with(['addressSubj' => function ($q) {
                        $q->select('id', 'subj_id', 'district_id')
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
            ->paginate(7);

        $paginated->getCollection()->transform(function ($obj) {
            $obj->subjs->transform(function ($subj) {
                // Загружаем фото
                $subj->load([
                    'imgSubjFirst:subj_id,path',
                    'imgSubjs' => function ($q) {
                        $q->select('subj_id', 'path')
                            ->orderBy('position')
                            ->take(5);
                    }
                ]);

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
                    'district_name' => $districtName // Строка с названием района субъекта
                ];
            });

            $obj->groupAddressObjs->transform(function ($groupAddressObjs) {
                $groupAddressObjs->load([
                    'district:id,name',
                ]);

                return [
                    'id' => $groupAddressObjs->district->id,
                    'name' => $groupAddressObjs->district->name,
                ];
            });

            return [
                'obj_id' => $obj->id,
                'user_id' => $obj->user_id,
                'name_obj' => $obj->name_obj,
                'phone_obj' => $obj->phone_obj,
                'subjs_data' => $obj->subjs->toArray(),
                'details_obj' => $obj->detailsObj->toArray(),
                'districts' => $obj->groupAddressObjs->toArray(),
                'districts_names' => $obj->groupAddressObjs->pluck('district.name')->toArray(),
            ];
        });

        return $paginated;
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