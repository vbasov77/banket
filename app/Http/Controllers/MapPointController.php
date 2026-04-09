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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\Translation\t;

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
        $groups = $this->mapService->findMap();

        return view('map.index', compact('groups'));
    }


    public
    function create(Request $request)
    {
        $subj = Subj::where('id', (int)$request->id)->first();

        return view('map.create', ['subj' => $subj]);
    }

    public function edit(Request $request)
    {
        $subjId = $request->id;
        $map = AddressSubj::where('subj_id', $subjId)->first();

        if ($map) {
            return view('map.edit', ['map' => $map]);
        } else {
            return redirect()->route('map.create', ['id' => $subjId]);
        }
    }


    public function addSubjectToMap(Request $request)
    {
        // Валидация входных данных
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'street' => 'required|string|max:255',
            'houseNumber' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'subj_id' => 'required|integer|exists:subjs,id',
            'obj_id' => 'required|integer'
        ]);

        $subjId = (int)$validated['subj_id'];
        $objId = (int)$validated['obj_id']; // Получаем ID объекта

        // Проверяем, не существует ли уже адрес для этого субъекта
        if (AddressSubj::where('subj_id', $subjId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Address already exists']);
        }

        DB::beginTransaction();

        try {
            // Создаём POINT-значение для location (только для поиска групп)
            $location = DB::raw("POINT({$validated['longitude']}, {$validated['latitude']})");

            $newSubject = [
                'city_id' => (int)$validated['city_id'],
                'district_id' => (int)$validated['district_id'],
                'address' => $validated['street'] . '; ' . $validated['houseNumber'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'subj_id' => $subjId,
            ];

            // Ищем группу в радиусе 50 метров ДЛЯ КОНКРЕТНОГО ОБЪЕКТА
            $assignedGroup = GroupAddressObj::selectRaw(
                '*, ST_Distance_Sphere(location, POINT(?, ?)) AS distance_meters',
                [$validated['longitude'], $validated['latitude']]
            )
                ->where('obj_id', $objId) // ВАЖНО: фильтруем только группы нужного объекта
                ->havingRaw('ST_Distance_Sphere(location, POINT(?, ?)) <= 50', [$validated['longitude'], $validated['latitude']])
                ->orderBy('distance_meters')
                ->first();

            if ($assignedGroup) {
                // Добавляем субъекта в существующую группу нужного объекта
                AddressSubj::create([
                    'city_id' => $newSubject['city_id'],
                    'district_id' => $newSubject['district_id'],
                    'address' => $newSubject['address'],
                    'latitude' => $newSubject['latitude'],
                    'longitude' => $newSubject['longitude'],
                    'group_id' => $assignedGroup->id,
                    'subj_id' => $subjId
                ]);
            } else {
                // Создаём новую группу для конкретного объекта с location
                $newGroup = GroupAddressObj::create([
                    'city_id' => $newSubject['city_id'],
                    'district_id' => $newSubject['district_id'],
                    'address' => $newSubject['address'],
                    'latitude' => $newSubject['latitude'],
                    'longitude' => $newSubject['longitude'],
                    'location' => $location,
                    'obj_id' => $objId // Привязываем группу к конкретному объекту
                ]);

                // Добавляем субъект в новую группу
                AddressSubj::create([
                    'city_id' => $newSubject['city_id'],
                    'district_id' => $newSubject['district_id'],
                    'address' => $newSubject['address'],
                    'latitude' => $newSubject['latitude'],
                    'longitude' => $newSubject['longitude'],
                    'group_id' => $newGroup->id,
                    'subj_id' => $subjId
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Адрес добавлен']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding subject to map: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding subject to map'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $subjs = AddressSubj::where('subj_id', $request->id)->get();
            if (!empty($subjs)) {
                if (count($subjs) == 1) {
                    AddressSubj::where('id', $subjs[0]->id)->delete();
                    GroupAddressObj::where('id', $subjs[0]->group_id)->delete();
                } else {
                    AddressSubj::where('id', $subjs[0]->id)->delete();
                }
            }

            return response()->json(['success' => true, 'message' => "Адрес удалён..."]);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => $e]);
        }

    }


}

