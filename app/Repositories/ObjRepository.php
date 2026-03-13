<?php


namespace App\Repositories;


use App\Models\Obj;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObjRepository extends Repository
{
    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
//        return Obj::where('id', $id)->first();
        return Obj::with([
            'detailsObj', // если нужны поля — дополните select
            'subjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_from', 'capacity_to', 'site_type', 'text_subj', 'published', 'id')
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
                            'capacity_from' => $subj->capacity_from,
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

    public function findObjByUserId()
    {
        return Obj::where('user_id', Auth::user()->id)->first();
    }

    /**
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function findMyObj(int $userId)
    {
        return Obj::with([
            'detailsObj', // если нужны поля — дополните select
            'subjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_from', 'capacity_to', 'site_type', 'text_subj', 'published', 'id')
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
                            'capacity_from' => $subj->capacity_from,
                            'capacity_to' => $subj->capacity_to,
                            'site_type' => $subj->site_type,
                            'text_subj' => $subj->text_subj,
                            'published' => $subj->published,
                            'path' => $subj->imgSubjFirst ? $subj->imgSubjFirst->path : null,
//                            'image_paths' => $subj->imgSubjs->pluck('path')->toArray()
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
                    'alcohol', 'payment_methods', 'text_obj');
            },
            'subjs' => function ($query) {
                $query->select('id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_from', 'capacity_to', 'site_type', 'features', 'text_subj');
            }
        ])
            ->select('objs.id', 'objs.user_id', 'objs.name_obj', 'objs.phone_obj')
            ->paginate(7);

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
                    'capacity_from' => $subj->capacity_from,
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


    /**
     * @param int $id
     * @return mixed
     */
    public function findByIdOnlyObj(int $id): mixed
    {
        return Obj::where('id', $id)->first();
    }

}