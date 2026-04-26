<?php


namespace App\Repositories;


use App\Models\GroupAddressObj;
use App\Models\Obj;
use App\Models\Subj;
use App\Services\VkService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubjRepository extends Repository
{

    /**
     * @return int|null
     * @throws \Exception
     */
    public function findIdObjByUserId(): ?int
    {
        try {
            return Obj::where('user_id', auth()->id())->value('id');
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ObjRepository@findIdObjByUserId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'user_id' => auth()->id()
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ObjRepository@findIdObjByUserId: ' . $e->getMessage(),
                [
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }


    public function findByIdForEdit(int $id)
    {
        return Subj::where('id', $id)->first();

    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param int $excludeObjId
     * @return array
     * @throws \Exception
     */
    public function findNearestObjects(float $latitude, float $longitude, int $excludeObjId): array
    {
        try {
            $groups = GroupAddressObj::select([
                'group_address_objs.id',
                'group_address_objs.obj_id',
                'group_address_objs.city_id',
                'group_address_objs.district_id',
                'group_address_objs.address',
                'group_address_objs.latitude',
                'group_address_objs.longitude',
                DB::raw("ROUND(ST_Distance_Sphere(group_address_objs.location, POINT(?, ?)) / 1000, 1) AS distance_km"),
                DB::raw("(
        SELECT img_subj.path
        FROM img_subj
        JOIN subjs ON img_subj.subj_id = subjs.id
        JOIN address_subjs ON subjs.id = address_subjs.subj_id
        WHERE address_subjs.group_id = group_address_objs.id
          AND address_subjs.city_id = group_address_objs.city_id
          AND address_subjs.district_id = group_address_objs.district_id
        ORDER BY img_subj.position ASC, img_subj.id ASC
        LIMIT 1
    ) AS photo_path"),
                DB::raw("(SELECT objs.name_obj FROM objs WHERE objs.id = group_address_objs.obj_id LIMIT 1) AS name_obj")
            ])
                ->setBindings([$longitude, $latitude])
                ->where('group_address_objs.obj_id', '!=', $excludeObjId)
                ->having('distance_km', '<=', 50)
                ->orderBy('distance_km')
                ->limit(5)
                ->get()
                ->toArray();

            return $this->formatResults($groups);


        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в SubjRepository@findNearestObjects: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'exclude_obj_id' => $excludeObjId
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в SubjRepository@findNearestObjects: ' . $e->getMessage(),
                [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'exclude_obj_id' => $excludeObjId,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param array $groups
     * @return array
     */
    private function formatResults(array $groups): array
    {
        // Логика форматирования результатов
        return array_map(function ($group) {
            return [
                'group_id' => $group['id'],
                'obj_id' => $group['obj_id'],
                'name_obj' => $group['name_obj'],
                'city_id' => $group['city_id'],
                'district' => $group['district'] ?? [],
                'address' => $group['address'],
                'latitude' => $group['latitude'],
                'longitude' => $group['longitude'],
                'path' => $group['photo_path'],
                'distance_km' => $group['distance_km'] ?? 0,
            ];
        }, $groups);
    }

    /**
     * @param int $id
     * @return array|null
     * @throws \JsonException
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT
    s.id AS subj_id,
    s.name_subj,
    s.minimum_cost,
    s.per_person,
    s.capacity_to,
    s.furshet,
    s.site_type,
    s.features,
    s.text_subj,
    s.published,
    gao.latitude,
    gao.longitude,  
    gao.city_id,  
    gao.district_id,  
    asub.address,
    d.name,
    CASE
        WHEN asub.subj_id IS NOT NULL THEN TRUE
        WHEN aobj.obj_id IS NOT NULL THEN TRUE
        ELSE FALSE
    END AS map,
    -- Флаг «в избранном» для текущего пользователя
    EXISTS(
        SELECT 1
        FROM favorites_subj fs
        WHERE fs.subj_id = s.id
          AND fs.user_id = ?  -- Плейсхолдер вместо жёсткого значения
    ) AS is_favorite,
    JSON_OBJECT(
        'obj_id', o.id,
        'user_id', o.user_id,
        'name_obj', o.name_obj,
        'phone_obj', o.phone_obj
    ) AS obj_json,
    IF(
        do.for_events IS NOT NULL OR
        do.kitchen IS NOT NULL OR do.service IS NOT NULL OR
        do.alcohol IS NOT NULL OR do.payment_methods IS NOT NULL OR
        do.text_obj IS NOT NULL,
        JSON_OBJECT(
            'for_events', do.for_events,
            'kitchen', do.kitchen,
            'service', do.service,
            'alcohol', do.alcohol,
            'payment_methods', do.payment_methods,
            'text_obj', do.text_obj
        ),
        NULL
    ) AS details_obj_json,
    (
        SELECT JSON_ARRAYAGG(path)
        FROM img_subj
        WHERE subj_id = s.id
    ) AS image_paths_json,
    (
        SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'subj_id', rs.id,
                'name_subj', rs.name_subj,
                'image_path', (
                    SELECT path
                    FROM img_subj
                    WHERE subj_id = rs.id
                    ORDER BY id ASC
                    LIMIT 1
                ),
                'capacity_to', rs.capacity_to,
                'minimum_cost', rs.minimum_cost
            )
        )
        FROM subjs rs
        WHERE rs.obj_id = s.obj_id
          AND rs.id != s.id
    ) AS related_subjs_json
FROM subjs s
LEFT JOIN objs o ON s.obj_id = o.id
LEFT JOIN details_obj do ON o.id = do.obj_id
LEFT JOIN address_subjs asub ON s.id = asub.subj_id
LEFT JOIN address_objs aobj ON o.id = aobj.obj_id
LEFT JOIN group_address_objs gao ON o.id = gao.obj_id
LEFT JOIN districts d ON gao.district_id = d.id 
WHERE s.id = ?  -- ID ресторана
LIMIT 1;";
            $userId = Auth::id();
            $results = DB::select($sql, [$userId, $id]);

            // DB::select возвращает массив, даже если одна строка
            if (empty($results)) {
                return null;
            }

            $result = $results[0];

            return [
                'subj_id' => $result->subj_id,
                'name_subj' => $result->name_subj,
                'address' => $result->address,
                'district_id' => $result->district_id,
                'district_name' => $result->name,
                'minimum_cost' => $result->minimum_cost,
                'per_person' => $result->per_person,
                'capacity_to' => $result->capacity_to,
                'furshet' => $result->furshet,
                'site_type' => $this->parseJsonField($result->site_type),
                'features' => $this->parseJsonField($result->features),
                'text_subj' => $result->text_subj,
                'published' => $result->published,
                'map' => (bool)$result->map,
                'obj' => $this->parseJsonField($result->obj_json),
                'details_obj' => $this->parseJsonField($result->details_obj_json),
                'image_paths' => $this->parseJsonArray($result->image_paths_json),
                'related_subjs' => $this->parseJsonArray($result->related_subjs_json),
                'is_favorite' => $result->is_favorite,
                'latitude' => $result->latitude,
                'longitude' => $result->longitude,
            ];
        } catch (QueryException $e) {
            throw $e;
        }
    }


    private function parseJsonField(?string $json): ?array
    {
        if ($json === null) {
            return null;
        }

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Безопасное преобразование JSON-массива в PHP-массив
     * Гарантирует возврат массива даже для NULL-значений
     *
     * @param string|null $jsonArray
     * @return array
     */
    private function parseJsonArray(?string $jsonArray): array
    {
        if ($jsonArray === null) {
            return [];
        }

        $decoded = json_decode($jsonArray, true, 512, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : [];
    }


    /**
     * @param array $array
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function update(array $array, int $id): void
    {
        try {
            DB::table('subjs')->where('id', $id)->update($array);
        } catch (QueryException $e) {
            Log::channel('error_file')->error('Database update failed in SubjRepository', [
                'exception' => $e->getMessage(),
                'subj_id' => $id,
                'update_data' => $array,
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error in SubjRepository update', [
                'exception' => $e->getMessage(),
                'subj_id' => $id
            ]);
            throw $e;
        }
    }


    /**
     * @param int $id
     * @return bool
     */
    public function existsImg(int $id): bool
    {
        return DB::table('img_subj')->where('subj_id', $id)->exists();
    }



}