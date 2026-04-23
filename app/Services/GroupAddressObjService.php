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
}
