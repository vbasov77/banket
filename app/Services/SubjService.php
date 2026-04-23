<?php


namespace App\Services;

use App\Models\GroupAddressObj;
use App\Models\ImgSubj;
use App\Models\Obj;
use App\Models\Subj;
use App\Repositories\ObjRepository;
use App\Repositories\SubjRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;


class SubjService extends Service
{
    private SubjRepository $subjRepository;

    public function __construct(SubjRepository $subjRepository)
    {
        $this->subjRepository = $subjRepository;
    }

    /**
     * @param string $nameSubj
     * @param int $objId
     * @param array $data
     * @return array
     */
    public function createSubj(string $nameSubj, int $objId, array $data): array
    {
        try {
            // Проверка на дубликат
            $existingSubj = Subj::where('name_subj', $nameSubj)
                ->where('obj_id', $objId)
                ->first();

            if ($existingSubj) {
                Log::channel('error_file')->error(
                    'Попытка создания дубликата субъекта',
                    [
                        'name_subj' => $nameSubj,
                        'obj_id' => $objId,
                        'user_id' => auth()->id(),
                        'existing_subj_id' => $existingSubj->id
                    ]
                );
                return [
                    'exists' => true,
                    'success' => false,
                    'subj' => null
                ];
            }

            // Создание нового субъекта
            $subj = Subj::create($data);

            if (!$subj) {
                throw new \RuntimeException('Не удалось создать субъект в базе данных');
            }

            return [
                'exists' => false,
                'success' => true,
                'subj' => $subj
            ];
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в SubjService@createSubj: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'input_data' => $data,
                    'user_id' => auth()->id()
                ]
            );
            throw new \RuntimeException('Ошибка базы данных при создании субъекта', 0, $e);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в SubjService@createSubj: ' . $e->getMessage(),
                [
                    'input_data' => $data,
                    'name_subj' => $nameSubj,
                    'obj_id' => $objId,
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @return int|null
     * @throws AuthenticationException
     */
    public function findIdObjByUserId(): ?int
    {
        try {
            // Проверка авторизации пользователя
            if (!auth()->check()) {
                throw new AuthenticationException('Пользователь не авторизован');
            }

            $objId = $this->subjRepository->findIdObjByUserId();

            if ($objId === null) {
                Log::channel('error_file')->error(
                    'Объект не найден для пользователя',
                    [
                        'user_id' => auth()->id()
                    ]
                );
                // Это не ошибка — просто объект не найден, возвращаем null
            }

            return $objId;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ObjService@findIdObjByUserId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'user_id' => auth()->id()
                ]
            );
            throw new \RuntimeException('Ошибка базы данных при поиске объекта', 0, $e);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ObjService@findIdObjByUserId: ' . $e->getMessage(),
                [
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return array|null
     * @throws \JsonException
     */
    public function findById(int $id): ?array
    {
        try {
            $result = $this->subjRepository->findById($id);

            if (!$result) {
                Log::channel('error_file')->error(
                    'Subject not found in SubjService@findById: ' . $id
                );
            }

            return $result;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in SubjService@findById: ' .
                $e->getMessage() . ' | Subject ID: ' . $id
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in SubjService@findById: ' .
                $e->getMessage() . ' | Subject ID: ' . $id
            );
            throw $e;
        }
    }


    /**
     * Поиск 5 ближайших объектов по координатам, исключая объект с указанным subj_id
     * Использование геопространственных функций БД
     *
     * @param float $latitude Широта точки поиска
     * @param float $longitude Долгота точки поиска
     * @param int $excludeObjId
     * @return array
     */
    public function findNearestObjects(float $latitude, float $longitude, int $excludeObjId): array
    {
        try {
            // Валидация входных координат
            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                Log::channel('error_file')->error(
                    'Некорректные координаты в SubjService@findNearestObjects',
                    [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'exclude_obj_id' => $excludeObjId
                    ]
                );
                throw new \InvalidArgumentException('Invalid coordinates');
            }

            return $this->subjRepository->findNearestObjects($latitude, $longitude, $excludeObjId);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в SubjService@findNearestObjects: ' . $e->getMessage(),
                [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'exclude_obj_id' => $excludeObjId,
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
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
     * @param array $data
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function update(array $data, int $id): void
    {
        try {
            $this->subjRepository->update($data, $id);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in SubjService update', [
                'exception' => $e->getMessage(),
                'subj_id' => $id,
                'data' => $data,
                'user_id' => auth()->id() ?? 'guest'
            ]);
            throw $e; // Перебрасываем исключение дальше
        }
    }


    public function findMySubjs(int $objId)
    {
        return $this->subjRepository->findMySubjs($objId);
    }

}

