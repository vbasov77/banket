<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

            // 1. Детали группы: адрес, координаты, obj, details_obj, obj_features, акции
            $groupDetailsSql = "
            SELECT
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
                dis.name AS district_name,
                IF(
                    do.for_events IS NOT NULL OR do.kitchen IS NOT NULL OR do.service IS NOT NULL
                    OR do.alcohol IS NOT NULL OR do.payment_methods IS NOT NULL OR do.bring_with_you IS NOT NULL
                    OR do.text_obj IS NOT NULL,
                    JSON_OBJECT(
                        'for_events', do.for_events,
                        'kitchen', do.kitchen,
                        'service', do.service,
                        'alcohol', do.alcohol,
                        'payment_methods', do.payment_methods,
                        'bring_with_you', do.bring_with_you,
                        'text_obj', do.text_obj
                    ),
                    NULL
                ) AS details_obj_json,
                IF(
                    ofeat.id IS NOT NULL,
                    JSON_OBJECT(
                        'banquet_note', ofeat.banquet_note,
                        'prepayment', ofeat.prepayment,
                        'textile_package', ofeat.textile_package,
                        'textile_colors', ofeat.textile_colors,
                        'tables', ofeat.tables,
                        'loud_music', ofeat.loud_music,
                        'parking', ofeat.parking,
                        'pier', ofeat.pier,
                        'equipment', ofeat.equipment,
                        'kids', ofeat.kids,
                        'interior', ofeat.interior,
                        'location', ofeat.location
                    ),
                    NULL
                ) AS obj_features_json,
                -- Акции объекта
                (
                    SELECT JSON_ARRAYAGG(
                        JSON_OBJECT('id', act.id, 'actions', act.actions)
                    )
                    FROM actions act
                    WHERE act.obj_id = o.id
                ) AS actions_json
            FROM group_address_objs gao
            LEFT JOIN objs o ON gao.obj_id = o.id
            LEFT JOIN details_obj do ON o.id = do.obj_id
            LEFT JOIN obj_features ofeat ON o.id = ofeat.obj_id
            LEFT JOIN districts dis ON gao.district_id = dis.id
            WHERE gao.id = ?
            LIMIT 1;
        ";

            $groupResults = DB::select($groupDetailsSql, [$groupId]);
            if (empty($groupResults)) {
                return [
                    'group_details' => null,
                    'subjs' => []
                ];
            }

            $groupResult = $groupResults[0];

            $groupDetails = [
                'group_id'      => $groupResult->group_id,
                'city_id'       => $groupResult->city_id,
                'district_id'   => $groupResult->district_id,
                'district_name' => $groupResult->district_name,
                'address'       => $groupResult->address,
                'latitude'      => $groupResult->latitude,
                'longitude'     => $groupResult->longitude,
                'obj'           => [
                    'id'         => $groupResult->obj_id,
                    'user_id'    => $groupResult->user_id,
                    'name_obj'   => $groupResult->name_obj,
                    'phone_obj'  => $groupResult->phone_obj,
                ],
                'details_obj'   => $this->parseJsonField($groupResult->details_obj_json),
                'obj_features'  => $this->parseJsonField($groupResult->obj_features_json),
                'actions'       => $this->parseJsonArray($groupResult->actions_json ?? '[]'),
            ];

            // 2. Залы группы: с метро, картинками, related_subjs
            $subjsSql = "
            SELECT
                s.id AS subj_id,
                s.name_subj,
                s.minimum_cost,
                s.per_person,
                s.capacity_to,
                s.furshet,
                s.site_type,
                s.published,
                asub.latitude,
                asub.longitude,
                EXISTS(
                    SELECT 1
                    FROM favorites_subj fs
                    WHERE fs.subj_id = s.id
                      AND fs.user_id = ?
                ) AS is_favorite,
                CASE
                    WHEN asub.subj_id IS NOT NULL THEN TRUE
                    ELSE FALSE
                END AS map,
                (
                    SELECT JSON_ARRAYAGG(small_img)
                    FROM img_ban_subj
                    WHERE subj_id = s.id
                ) AS image_paths_json,
                (
                    SELECT JSON_ARRAYAGG(small_img)
                    FROM img_ban_subj
                    WHERE subj_id = s.id
                ) AS small_image_paths_json,
                -- Метро для зала (из subj_near_metro)
                (
                    SELECT JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'metro_station_id', snm.metro_station_id,
                            'distance_km', snm.distance_km,
                            'rank', snm.rank,
                            'station_name', ms.name
                        )
                    )
                    FROM subj_near_metro snm
                    JOIN metro_stations ms ON ms.id = snm.metro_station_id
                    WHERE snm.subj_id = s.id
                    ORDER BY snm.rank ASC
                ) AS nearest_metros_json,
                -- Похожие залы по obj_id
                (
                    SELECT JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'subj_id', rs.id,
                            'name_subj', rs.name_subj,
                            'image_path', (
                                SELECT small_img
                                FROM img_ban_subj
                                WHERE subj_id = rs.id
                                ORDER BY id ASC
                                LIMIT 1
                            ),
                            'capacity_to', rs.capacity_to,
                            'minimum_cost', rs.minimum_cost
                        )
                    )
                    FROM subjs rs
                    WHERE rs.obj_id = o.id
                      AND rs.id != s.id
                      AND rs.published = 1
                ) AS related_subjs_json
            FROM subjs s
            JOIN address_subjs asub ON s.id = asub.subj_id
            LEFT JOIN objs o ON s.obj_id = o.id
            WHERE asub.group_id = ?
            ORDER BY s.name_subj;
        ";

            $subjsResults = DB::select($subjsSql, [$userId, $groupId]);

            $subjs = [];
            foreach ($subjsResults as $result) {
                $subjs[] = [
                    'id'                => $result->subj_id,
                    'name_subj'         => $result->name_subj,
                    'minimum_cost'      => $result->minimum_cost,
                    'per_person'        => $result->per_person,
                    'capacity_to'       => $result->capacity_to,
                    'furshet'           => $result->furshet,
                    'site_type'         => $this->parseJsonField($result->site_type),
                    'published'         => (bool)$result->published,
                    'latitude'          => $result->latitude,
                    'longitude'         => $result->longitude,
                    'is_favorite'       => (bool)$result->is_favorite,
                    'map'               => (bool)$result->map,
                    'image_paths'       => $this->parseJsonArray($result->image_paths_json),
                    'small_image_paths'   => $this->parseJsonArray($result->small_image_paths_json),
                    'nearest_metros'    => $this->parseJsonArray($result->nearest_metros_json ?? '[]'),
                    'related_subjs'     => $this->parseJsonArray($result->related_subjs_json ?? '[]'),
                ];
            }

            return [
                'group_details' => $groupDetails,
                'subjs'         => $subjs,
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error('GroupAddressObjRepository@findSubjectsByGroupId: ошибка запроса', [
                'group_id' => $groupId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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

