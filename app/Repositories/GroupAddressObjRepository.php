<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupAddressObjRepository
{
    /**
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT
    gao.id AS group_id,
    gao.city_id,
    gao.district_id,
    gao.address,
    gao.latitude,
    gao.longitude,
    o.id AS obj_id,
    o.name_obj,
    o.phone_obj
FROM group_address_objs gao
LEFT JOIN objs o ON gao.obj_id = o.id
WHERE gao.id = ?
LIMIT 1;";

            $results = DB::select($sql, [$id]);

            if (empty($results)) {
                return null;
            }

            $result = $results[0];

            return [
                'group_id' => $result->group_id,
                'city_id' => $result->city_id,
                'district_id' => $result->district_id,
                'address' => $result->address,
                'latitude' => $result->latitude,
                'longitude' => $result->longitude,
                'obj' => [
                    'id' => $result->obj_id,
                    'name_obj' => $result->name_obj,
                    'phone_obj' => $result->phone_obj,
                ],
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            throw $e;
        }
    }

    /**
     * @param int $groupId
     * @return array
     * @throws \JsonException
     */
    public function findSubjectsByGroupId(int $groupId): array
    {
        try {
            $userId = Auth::id();

            // Запрос для получения деталей группы (включая details_obj)
            $groupDetailsSql = "SELECT
            gao.id AS group_id,
            gao.city_id,
            gao.district_id,
            gao.address,
            gao.latitude,
            gao.longitude,
            o.id AS obj_id,
            o.user_id,
            o.name_obj,
            o.phone_obj,
            dis.name,
            IF(
                do.for_events IS NOT NULL OR
                do.kitchen IS NOT NULL OR do.service IS NOT NULL OR
                do.alcohol IS NOT NULL OR
                do.more IS NOT NULL OR do.payment_methods IS NOT NULL OR
                do.text_obj IS NOT NULL,
                JSON_OBJECT(
                    'for_events', do.for_events,
            'kitchen', do.kitchen,
            'service', do.service,
            'alcohol', do.alcohol,
            'more', do.more,
            'payment_methods', do.payment_methods,
            'text_obj', do.text_obj
        ),
        NULL
    ) AS details_obj_json
FROM group_address_objs gao
LEFT JOIN objs o ON gao.obj_id = o.id
LEFT JOIN details_obj do ON o.id = do.obj_id
LEFT JOIN districts dis ON gao.district_id = dis.id
WHERE gao.id = ?
LIMIT 1;";

            $groupResults = DB::select($groupDetailsSql, [$groupId]);
            if (empty($groupResults)) {
                return [
                    'group_details' => null,
                    'subjs' => []
                ];
            }

            $groupResult = $groupResults[0];
            $groupDetails = [
                'group_id' => $groupResult->group_id,
                'city_id' => $groupResult->city_id,
                'district_id' => $groupResult->district_id,
                'district_name' => $groupResult->name,
                'address' => $groupResult->address,
                'latitude' => $groupResult->latitude,
                'longitude' => $groupResult->longitude,
                'obj' => [
                    'id' => $groupResult->obj_id,
                    'user_id' => $groupResult->user_id,
                    'name_obj' => $groupResult->name_obj,
                    'phone_obj' => $groupResult->phone_obj,
                ],
                'details_obj' => $this->parseJsonField($groupResult->details_obj_json),
            ];

            // Запрос для получения субъектов группы (без details_obj)
            $subjsSql = "SELECT
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
            asub.latitude,
            asub.longitude,
            EXISTS(
                SELECT 1
                FROM favorites_subj fs
                WHERE fs.subj_id = s.id
                  AND fs.user_id = ?
            ) AS is_favorite,
            (
                SELECT JSON_ARRAYAGG(small_img)
                FROM img_ban_subj
                WHERE subj_id = s.id
            ) AS image_paths_json
        FROM subjs s
        JOIN address_subjs asub ON s.id = asub.subj_id
        JOIN group_address_objs gao ON asub.group_id = gao.id
        LEFT JOIN objs o ON s.obj_id = o.id
        WHERE asub.group_id = ?
        ORDER BY s.name_subj;";

            $subjsResults = DB::select($subjsSql, [$userId, $groupId]);

            $subjs = [];
            foreach ($subjsResults as $result) {
                $subjs[] = [
                    'id' => $result->subj_id,
                    'name_subj' => $result->name_subj,
                    'minimum_cost' => $result->minimum_cost,
                    'per_person' => $result->per_person,
                    'capacity_to' => $result->capacity_to,
                    'furshet' => $result->furshet,
                    'site_type' => $this->parseJsonField($result->site_type),
                    'features' => $this->parseJsonField($result->features),
                    'text_subj' => $result->text_subj,
                    'published' => $result->published,
                    'latitude' => $result->latitude,
                    'longitude' => $result->longitude,
                    'is_favorite' => (bool)$result->is_favorite,
                    'image_paths' => $this->parseJsonArray($result->image_paths_json),
                ];
            }

            return [
                'group_details' => $groupDetails,
                'subjs' => $subjs
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            throw $e;
        }
    }

    /**
     * Парсинг JSON‑поля
     */
    private function parseJsonField(?string $json): ?array
    {
        if (empty($json)) {
            return null;
        }

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Парсинг JSON‑массива
     */
    private function parseJsonArray(?string $jsonArray): array
    {
        if (empty($jsonArray) || $jsonArray === '[]') {
            return [];
        }

        $decoded = json_decode($jsonArray, true, 512, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : [];
    }
}

