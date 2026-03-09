<?php


namespace App\Repositories;


use App\Models\Obj;
use App\Models\Subj;
use App\Services\VkService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SubjRepository extends Repository
{
    private $vkService;

    /**
     * @param $vkService
     */
    public function __construct()
    {
        $this->vkService = new VkService();
    }


    public function findByIdForEdit(int $id)
    {
        return Subj::where('id', $id)->first();

    }

    public function findById(int $id): ?array
    {
        try {
            $sql = "
                SELECT
                    s.id AS subj_id,
                    s.name_subj,
            s.address_subj,
            s.minimum_cost,
            s.per_person,
            s.capacity_from,
            s.capacity_to,
            s.furshet,
            s.site_type,
            s.features,
            s.text_subj,
            s.published,
            CASE
                WHEN asub.subj_id IS NOT NULL THEN TRUE
                WHEN aobj.obj_id IS NOT NULL THEN TRUE
                ELSE FALSE
            END AS map,
            JSON_OBJECT(
                'obj_id', o.id,
                'user_id', o.user_id,
                'name_obj', o.name_obj,
                'address_obj', o.address_obj,
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
        'capacity_from', rs.capacity_from,
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
WHERE s.id = ?
LIMIT 1";

            $results = DB::select($sql, [$id]);

            // DB::select возвращает массив, даже если одна строка
            if (empty($results)) {
                return null;
            }

            $result = $results[0];

            return [
                'subj_id' => $result->subj_id,
                'name_subj' => $result->name_subj,
                'address_subj' => $result->address_subj,
                'minimum_cost' => $result->minimum_cost,
                'per_person' => $result->per_person,
                'capacity_from' => $result->capacity_from,
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
     */
    public function update(array $array, int $id): void
    {
        DB::table('subjs')->where('id', $id)->update($array);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function existsImg(int $id): bool
    {
        return DB::table('img_subj')->where('subj_id', $id)->exists();
    }

    public function findMySubjs(int $objId)
    {
        // 1. Получаем obj и subjs БЕЗ фото
        $obj = Obj::with([
            'detailsObj:*',
            'subjsAll' => function ($query) {
                $query->select([
                    'id', 'obj_id', 'name_subj', 'address_subj',
                    'minimum_cost', 'per_person', 'capacity_from',
                    'capacity_to', 'site_type', 'text_subj', 'published', 'features'
                ]);
            },
            'user:*',
            'imgObj:*'
        ])
            ->where('id', $objId)
            ->select(['id', 'user_id', 'name_obj', 'address_obj', 'phone_obj'])
            ->first();

        if (!$obj) {
            return null;
        }

        // 2. Вручную загружаем primaryImg для КАЖДОГО subj
        foreach ($obj->subjsAll as $subj) {
            $subj->primaryImg = $subj->primaryImg() // используем отношение
            ->select(['subj_id', 'path', 'position']) // явно указываем поля
            ->first(); // получаем одно фото
        }

        return $obj->toArray();
    }



}