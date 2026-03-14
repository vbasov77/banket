<?php


namespace App\Repositories;


use App\Models\GroupAddressObj;
use Illuminate\Http\JsonResponse;

class MapRepository extends Repository
{
    /**
     * @return JsonResponse
     */
    public function getMapData(): JsonResponse
    {
        $groups = GroupAddressObj::with('subjects:id,address,latitude,longitude,group_id,subj_id')
            ->select('id', 'address as title', 'latitude', 'longitude')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'latitude' => $group->latitude,
                    'longitude' => $group->longitude,
                    'subjects_count' => $group->subjects->count(),
                    'subjects' => $group->subjects->map(function ($subject) {
                        return [
                            'id' => $subject->id,
                            'address' => $subject->address,
                            'latitude' => $subject->latitude,
                            'longitude' => $subject->longitude,
                            'subj_id' => $subject->subj_id
                        ];
                    })
                ];
            });

        return response()->json($groups);
    }

    public function findMap()
    {
        return GroupAddressObj::with([
            'subjects' => function ($query) {
                $query->select('id', 'group_id', 'subj_id', 'address', 'longitude', 'latitude');
            },
            'subjects.subj' => function ($query) {
                $query->select(
                    'id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_to', 'furshet', 'site_type',
                    'features', 'text_subj'
                );
            },
            'subjects.subj.obj' => function ($query) {
                $query->select('id', 'user_id', 'name_obj', 'phone_obj');
            }
        ])
            ->select('id', 'address as title', 'latitude', 'longitude')
            ->get()
            ->map(function ($group) {
                // Собираем уникальные объекты группы
                $uniqueObjects = collect($group->subjects)
                    ->pluck('subj.obj')
                    ->filter() // Убираем null
                    ->unique('id') // Оставляем только уникальные по ID
                    ->values(); // Переиндексируем коллекцию

                return [
                    'id' => $group->id,
                    'title' => $group->title,
                    'latitude' => (float)$group->latitude,
                    'longitude' => (float)$group->longitude,
                    'subjects_count' => $group->subjects->count(),
                    'subjects' => $group->subjects->map(function ($addressSubj) {
                        $subj = $addressSubj->subj;
                        return [
                            'id' => $subj->id ?? null,
                            'name_subj' => $subj->name_subj ?? 'Не указано',
                            'minimum_cost' => $subj->minimum_cost ?? 'Не указана',
                            'per_person' => $subj->per_person ?? 'Не указано',
                            'capacity_to' => $subj->capacity_to ?? '?',
                            'furshet' => $subj->furshet ?? 'Не указано',
                            'site_type' => $subj->site_type ?? 'Не указан',
                            'features' => $subj->features ?? 'Нет данных',
                            'text_subj' => $subj->text_subj ?? 'Нет описания',
                            'address_data' => [
                                'address' => $addressSubj->address ?? 'Нет адреса',
                                'latitude' => (float)($addressSubj->latitude ?? 0),
                                'longitude' => (float)($addressSubj->longitude ?? 0)
                            ]
                        ];
                    })->toArray(),
                    // Добавляем уникальные объекты группы
                    'objects' => $uniqueObjects->map(function ($obj) {
                        return [
                            'id' => $obj->id,
                            'user_id' => $obj->user_id,
                            'name_obj' => $obj->name_obj,
                            'phone_obj' => $obj->phone_obj
                        ];
                    })->toArray()
                ];
            });

    }


}