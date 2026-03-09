<?php


namespace App\Repositories;


use App\Models\ImgObj;

class ImgObjRepository extends Repository
{
    public function findImgByObjId(int $id)
    {
        return ImgObj::where('obj_id', $id)->first();
    }

}