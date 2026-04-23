<?php

namespace App\Repositories;

use App\Models\AddressSubj;
use App\Models\GroupAddressObj;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class AddressSubjRepository extends Repository
{
    public function findBySubjId(int $subjId): ?array
    {
        try {
            $result = DB::table('address_subjs as asub')
                ->select('asub.*')
                ->where('asub.subj_id', $subjId)
                ->first();

            return $result ? (array)$result : null;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'Database error in AddressSubjRepository@findBySubjId: ' .
                $e->getMessage() . ' | Subj ID: ' . $subjId
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in AddressSubjRepository@findBySubjId: ' .
                $e->getMessage() . ' | Subj ID: ' . $subjId
            );
            throw $e;
        }
    }

    public function addressExistsForSubj(int $subjId): bool
    {
        return AddressSubj::where('subj_id', $subjId)->exists();
    }

    public function findGroupNearby(float $longitude, float $latitude, int $objId): ?GroupAddressObj
    {
        return GroupAddressObj::selectRaw(
            '*, ST_Distance_Sphere(location, POINT(?, ?)) AS distance_meters',
            [$longitude, $latitude]
        )
            ->where('obj_id', $objId)
            ->havingRaw('ST_Distance_Sphere(location, POINT(?, ?)) <= 50', [$longitude, $latitude])
            ->orderBy('distance_meters')
            ->first();
    }

    public function createAddressSubj(array $data): AddressSubj
    {
        return AddressSubj::create($data);
    }

    public function createGroupAddressObj(array $data)
    {
        return GroupAddressObj::create($data);
    }
}
