<?php

namespace App\Http\Controllers;

use App\Models\AddressSubj;
use App\Models\GroupAddressObj;
use App\Models\MapPoint;
use App\Models\Subj;
use App\Services\MapService;
use App\Services\SubjService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapPointController extends Controller
{
    private $mapService;
    private $subjService;

    public function __construct()
    {
        $this->mapService = new MapService();
        $this->subjService = new SubjService();
    }

    public function show(Request $request)
    {
        $subjId = $request->id;
        $map = AddressSubj::where('subj_id', $subjId)->first();
        $map['data_subj'] = $this->subjService->findById($subjId);

        return view('map.show', ['map' => $map]);
    }

    public function index()
    {
        $points = MapPoint::all();
        return view('map.index', compact('points'));
    }

    public function getMapData()
    {
        $groups = $this->mapService->getMapData();

        return view('map.index', ['groups' => $groups]);
    }


    public function showMap()
    {
        $groups = GroupAddressObj::with([
            'subjects' => function ($query) {
                $query->select('id', 'group_id', 'subj_id', 'address', 'longitude', 'latitude');
            },
            'subjects.subj' => function ($query) {
                $query->select(
                    'id', 'obj_id', 'name_subj', 'minimum_cost', 'per_person',
                    'capacity_from', 'capacity_to', 'furshet', 'site_type',
                    'features', 'text_subj'
                );
            },
            'subjects.subj.obj' => function ($query) {
                $query->select('id', 'user_id', 'name_obj', 'address_obj', 'phone_obj');
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
                            'capacity_from' => $subj->capacity_from ?? '?',
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
                            'address_obj' => $obj->address_obj,
                            'phone_obj' => $obj->phone_obj
                        ];
                    })->toArray()
                ];
            });

        return view('map.index', compact('groups'));
    }


    public
    function create(Request $request)
    {
        $subj = Subj::where('id', (int)$request->id)->first();

        return view('map.create', ['subj' => $subj]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public
    function store(Request $request): JsonResponse
    {
        $request->validate([
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $addressArr = $this->mapService->parseAddress($request->input('address'));

        $point = AddressSubj::create([
            'address' => $addressArr,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'subj_id' => 2, // Для тестирования, если нет авторизации
        ]);

        return response()->json($point, 201);
    }

    public
    function addSubjectToMap(Request $request)
    {
        $subjId = (int)$request->subj_id;

        $newSubject = [
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'subj_id' => $subjId,
        ];

        // ИЩЕМ ГРУППЫ СРЕДИ ВСЕХ СУБЪЕКТОВ (убираем where('subj_id', $subjId))
        $allGroups = GroupAddressObj::all();
        $assignedGroup = null;

        foreach ($allGroups as $group) {
            $distance = $this->mapService->calculateDistance(
                $group->latitude,
                $group->longitude,
                $newSubject['latitude'],
                $newSubject['longitude']
            );

            if ($distance <= 50) {
                $assignedGroup = $group;
                break; // нашли подходящую группу — выходим из цикла
            }
        }

        if ($assignedGroup) {
            // Добавляем субъекта в существующую группу
            AddressSubj::create([
                'address' => $newSubject['address'],
                'latitude' => $assignedGroup->latitude, // используем центральные координаты группы
                'longitude' => $assignedGroup->longitude,
                'group_id' => $assignedGroup->id,
                'subj_id' => $subjId
            ]);
        } else {
            // Создаём новую группу для этого субъекта
            $newGroup = GroupAddressObj::create([
                'address' => $newSubject['address'],
                'latitude' => $newSubject['latitude'],
                'longitude' => $newSubject['longitude'],
                'subj_id' => $subjId,
            ]);

            AddressSubj::create([
                'address' => $newSubject['address'],
                'latitude' => $newSubject['latitude'],
                'longitude' => $newSubject['longitude'],
                'group_id' => $newGroup->id,
                'subj_id' => $subjId
            ]);
        }

        return redirect()->back();
    }


}

