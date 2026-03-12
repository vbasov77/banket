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


    public
    function addSubjectToMap(Request $request)
    {
        $subjId = (int)$request->subj_id;
        $subjBool = AddressSubj::where('subj_id', $subjId)->exists();

        if ($subjBool) {
            return response()->json(['success' => false, 'message' => 'Адрес уже существует']);
        }

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

        return response()->json(['success' => true, 'message' => 'Адрес добавлен']);
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

