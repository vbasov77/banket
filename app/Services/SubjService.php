<?php


namespace App\Services;

use App\Models\GroupAddressObj;
use App\Models\ImgSubj;
use App\Models\Obj;
use App\Models\Subj;
use App\Repositories\ObjRepository;
use App\Repositories\SubjRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;


class SubjService extends Service
{
    private $subjRepository;
    private $objRepository;

    public function __construct()
    {
        $this->subjRepository = new SubjRepository();
        $this->objRepository = new ObjRepository();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->subjRepository->findById($id);
    }

    /**
     * Поиск 5 ближайших объектов по координатам, исключая объект с указанным subj_id
     * Использование геопространственных функций БД
     *
     * @param float $latitude Широта точки поиска
     * @param float $longitude Долгота точки поиска
     * @param int $excludeSubjId ID субъекта для исключения из поиска
     * @return array
     */
    public function findNearestObjects(float $latitude, float $longitude, int $excludeObjId): array
    {
        // Валидация входных координат
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException('Invalid coordinates');
        }
        $groups = GroupAddressObj::with(['district' => function ($query) {
            $query->select('id', 'name')->get(); // выбираем только нужные поля из таблицы districts
        }])
            ->select([
                'obj_id',
                'city_id',
                'district_id', // вместо 'district' — указываем реальный столбец с foreign key
                'address',
                'latitude',
                'longitude',
                DB::raw("ST_Distance_Sphere(location, POINT($longitude, $latitude)) / 1000 AS distance_km")
            ])
            ->where('obj_id', '!=', $excludeObjId)
            ->having('distance_km', '<=', 5000)
            ->orderBy('distance_km')
            ->limit(5)
            ->get();

        return $this->formatResults($groups);
    }

    private function formatResults($groups): array
    {

        $result = [];
        $subjIds = $groups->pluck('obj_id')->toArray();

        if (empty($subjIds)) {
            return [];
        }

        // Массовая загрузка связанных данных (избегаем N+1 запросов)
        $subjsWithRelations = Obj::with(['subjects', 'details', 'groupAddressObjs'])
            ->whereIn('id', $subjIds)
            ->get()
            ->keyBy('id');
        // Загрузка фото (первое по позиции для каждого subj_id)
        $photos = ImgSubj::whereIn('subj_id', $subjIds)
            ->orderBy('position', 'asc')
            ->get()
            ->groupBy('subj_id');

        foreach ($groups as $group) {
            $objId = $group->obj_id;
            $obj = $subjsWithRelations->get($objId);

            if (!$obj) {
                continue;
            }

            $result[] = [
                'obj_id' => $objId,
                'name_obj' => $obj->name_obj,
                'address' => [
                    'city_id' => $group->city_id,
                    'district_id' => $group->district_id,
                    'district_name' => $group->district->name,
                    'address' => $group->address,
                    'latitude' => $group->latitude,
                    'longitude' => $group->longitude,
                    'distance_km' => round($group->distance_km, 2),
                ],
                'subj' => [
                    'id' => $obj->subjects[0]->id,
                    'name' => $obj->subjects[0]->name_subj,
                    'minimum_cost' => $obj->subjects[0]->minimum_cost,
                    'per_person' => $obj->subjects[0]->per_person,
                    'capacity_to' => $obj->subjects[0]->capacity_to,
                    'furshet' => $obj->subjects[0]->furshet,
                    'site_type' => $obj->subjects[0]->site_type,
                    'features' => $obj->subjects[0]->features,
                    'text' => $obj->subjects[0]->text_subj,
                    'published' => $obj->subjects[0]->published,
                ],
                'details' => $obj->details ? [
                    'kitchen' => $obj->details[0]->kitchen,
                    'text' => $obj->details[0]->text_obj,
                ] : null,
                'photo' => $this->getFirstPhoto($photos, $objId),
            ];
        }

        return $result;
    }

    private function getFirstPhoto($photosGrouped, $subjId)
    {
        if (!$photosGrouped->has($subjId)) {
            return null;
        }

        $firstPhoto = $photosGrouped->get($subjId)->first();
        return $firstPhoto ? $firstPhoto->path : null;
    }


    public function findByIdForEdit(int $id)
    {
        return $this->subjRepository->findByIdForEdit($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function existsImg(int $id): bool
    {
        return $this->subjRepository->existsImg($id);
    }

    /**
     * @param array $array
     * @param int $id
     * @return void
     */
    public function update(array $array, int $id): void
    {
        $this->subjRepository->update($array, $id);
    }

    public function findMySubjs(int $objId)
    {
        return $this->subjRepository->findMySubjs($objId);
    }

}

