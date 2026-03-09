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


}