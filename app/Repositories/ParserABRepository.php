<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ParserABRepository
{
    public function createObj(array $data): int
    {
        return DB::table('objs')->insertGetId($data);
    }

    public function createDetailsObj(array $data): void
    {
        DB::table('details_obj')->insert($data);
    }

    public function createObjFeatures(array $data): void
    {
        DB::table('obj_features')->insert($data);
    }

    public function createSubj(array $data): int
    {
        return DB::table('subjs')->insertGetId($data);
    }
}
