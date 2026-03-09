<?php


namespace App\Repositories;


use App\Models\DetailsObj;
use Illuminate\Support\Facades\DB;

class DetailsObjRepository extends Repository
{
    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return DetailsObj::where('id', $id)->first();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findByObjId(int $id)
    {
        return DetailsObj::where('obj_id', $id)->first();
    }

    /**
     * @param array $array
     * @return void
     */
    public function update(array $data): void
    {
        DetailsObj::where('id', $data['id'])->update($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data)
    {
        return DetailsObj::create($data);
    }
}