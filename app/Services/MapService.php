<?php


namespace App\Services;


use App\Models\Obj;
use App\Models\Subj;
use App\Repositories\AddressSubjRepository;
use App\Repositories\MapRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MapService extends Service
{

    private MapRepository $mapRepository;
    private AddressSubjRepository $addressSubjRepository;

    public function __construct(AddressSubjRepository $addressSubjRepository, MapRepository $mapRepository)
    {
        $this->addressSubjRepository = $addressSubjRepository;
        $this->mapRepository = $mapRepository;
    }


    /**
     * @param array $validatedData
     * @param int $userId
     * @return array|RedirectResponse
     */
    public function addSubjectToMap(array $validatedData, int $userId): array|RedirectResponse
    {
        $subjId = (int)$validatedData['subj_id'];
        $objId = (int)$validatedData['obj_id'];

        // Загружаем Subj и Obj для проверки прав
        $subj = Subj::with('obj')->find($subjId);

        if (!$subj) {
            return ['success' => false, 'message' => 'Субъект не найден', 'code' => 404];
        }

        // Проверка прав доступа через метод модели isAuthor()
        if (!$subj->isAuthor()) {
            Log::channel('error_file')->error('Unauthorized subj edit attempt', [
                'subj_id' => $subj->id,
                'user_id' => auth()->id(),
                'model_user_id' => 'null', // Всегда null для Subj
                'related_obj_user_id' => $subj->obj->user_id ?? 'null'
            ]);

            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для редактирования этого субъекта'
            ]);
        }

        // Проверяем, не существует ли уже адрес для этого субъекта
        if ($this->addressSubjRepository->addressExistsForSubj($subjId)) {
            return ['success' => false, 'message' => 'Address already exists'];
        }

        DB::beginTransaction();

        try {
            $location = DB::raw("POINT({$validatedData['longitude']}, {$validatedData['latitude']})");
            $newSubject = [
                'city_id' => (int)$validatedData['city_id'],
                'district_id' => (int)$validatedData['district_id'],
                'address' => $validatedData['street'] . '; ' . $validatedData['houseNumber'],
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
                'subj_id' => $subjId,
            ];

            $assignedGroup = $this->addressSubjRepository->findGroupNearby(
                $validatedData['longitude'],
                $validatedData['latitude'],
                $objId
            );

            if ($assignedGroup) {
                // Добавляем субъекта в существующую группу
                $this->addressSubjRepository->createAddressSubj([
                    'city_id' => $newSubject['city_id'],
                    'district_id' => $newSubject['district_id'],
                    'address' => $newSubject['address'],
                    'latitude' => $newSubject['latitude'],
                    'longitude' => $newSubject['longitude'],
                    'group_id' => $assignedGroup->id,
                    'subj_id' => $subjId
                ]);
            } else {
                // Создаём новую группу
                $newGroup = $this->addressSubjRepository->createGroupAddressObj([
                    'city_id' => $newSubject['city_id'],
                    'district_id' => $newSubject['district_id'],
                    'address' => $newSubject['address'],
                    'latitude' => $newSubject['latitude'],
                    'longitude' => $newSubject['longitude'],
                    'location' => $location,
                    'obj_id' => $objId
                ]);

                // Добавляем субъект в новую группу
                $this->addressSubjRepository->createAddressSubj([
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
            return ['success' => true, 'message' => 'Адрес добавлен'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error_file')->error('Error in MapService::addSubjectToMap', [
                'exception' => $e->getMessage(),
                'validated_data' => $validatedData,
                'user_id' => $userId
            ]);
            return ['success' => false, 'message' => 'Error adding subject to map', 'code' => 500];
        }
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function getMapData(): JsonResponse
    {
        try {
            return $this->mapRepository->getMapData();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in MapService@getMapData: ' . $e->getMessage()
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in MapService@getMapData: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // в метрах

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function findMap()
    {
        return $this->mapRepository->findMap();
    }


}