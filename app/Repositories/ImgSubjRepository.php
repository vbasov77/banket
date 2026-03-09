<?php


namespace App\Repositories;


use App\Models\ImgObj;
use App\Models\ImgSubj;

class ImgSubjRepository extends Repository
{
    public function findImgByObjId(int $id)
    {
        return ImgSubj::where('subj_id', $id)->orderBy('position', 'asc')->get();
    }

}