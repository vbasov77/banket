<?php

namespace App\Repositories;

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

            return $result ? (array) $result : null;
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
}
