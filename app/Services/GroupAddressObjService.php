<?php

namespace App\Services;

use App\Repositories\GroupAddressObjRepository;
use Illuminate\Support\Facades\Log;

class GroupAddressObjService
{
    private GroupAddressObjRepository $groupAddressObjRepository;

    public function __construct(GroupAddressObjRepository $groupAddressObjRepository)
    {
        $this->groupAddressObjRepository = $groupAddressObjRepository;
    }

    /**
     * Найти группу по ID
     */
    public function findById(int $id): ?array
    {
        try {
            $result = $this->groupAddressObjRepository->findById($id);

            if (!$result) {
                Log::channel('error_file')->error(
                    'Group not found in GroupAddressObjService@findById: ' . $id
                );
            }

            return $result;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in GroupAddressObjService@findById: ' .
                $e->getMessage() . ' | Group ID: ' . $id
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in GroupAddressObjService@findById: ' .
                $e->getMessage() . ' | Group ID: ' . $id
            );
            throw $e;
        }
    }

    /**
     * Найти все субъекты, принадлежащие группе
     */
    /**
     * Найти все субъекты и детали группы по ID группы
     */
    public function findSubjectsByGroupId(int $groupId): array
    {
        try {
            return $this->groupAddressObjRepository->findSubjectsByGroupId($groupId);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in GroupAddressObjService@findSubjectsByGroupId: ' .
                $e->getMessage() . ' | Group ID: ' . $groupId
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in GroupAddressObjService@findSubjectsByGroupId: ' .
                $e->getMessage() . ' | Group ID: ' . $groupId
            );
            throw $e;
        }
    }

    public function findMetaDescription(array $result): string
    {
        $groupDetails = $result['group_details'] ?? null;
        $subjs = $result['subjs'] ?? [];

        if (!$groupDetails || empty($groupDetails['obj']['name_obj'])) {
            return '';
        }

        $metaDescription = $groupDetails['obj']['name_obj'] . ". ";

        if (!empty($groupDetails['details_obj']['text_obj'])) {
            $text = strip_tags($groupDetails['details_obj']['text_obj']);
            $text = mb_substr($text, 0, 100);
            $metaDescription .= $text . ". ";
        }

        if (!empty($subjs)) {
            $subjNames = [];
            foreach ($subjs as $subj) {
                $subjNames[] = "«" . $subj['name_subj'] . "» на " . $subj['capacity_to'] . " гостей";
            }
            $metaDescription .= "Залы: " . implode(", ", $subjNames) . ". ";

            $firstSubj = $subjs[0];
            if (!empty($firstSubj['per_person'])) {
                $metaDescription .= "Цена от: " . $firstSubj['per_person'] . " руб. за человека. ";
            }
        }

        if (!empty($groupDetails['district_name'])) {
            $metaDescription .= "Район " . $groupDetails['district_name'] . ". ";
        }

        if (!empty($firstSubj['nearest_metros'])) {
            $metros = array_map(function ($m) {
                return $m['station_name'] . " (" . $m['distance_km'] . " км)";
            }, $firstSubj['nearest_metros']);
            $metaDescription .= "Ближайшие станции метро: " . implode(", ", $metros) . ".";
        }

        return trim($metaDescription);
    }



}
