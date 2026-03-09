<?php


namespace App\Services;

use App\Repositories\ObjRepository;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;


class ObjService extends Service
{
    private $objRepository;

    public function __construct()
    {
        $this->objRepository = new ObjRepository();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->objRepository->findById($id);
    }


    /**
     * @param int $userId
     * @return mixed
     */
    public function findByUserId(int $userId)
    {
        return $this->objRepository->findByUserId($userId);
    }

    /**
     * @param array $array
     * @param int $id
     * @return void
     */
    public function update(array $array, int $id): void
    {
        $this->objRepository->update($array, $id);
    }


    public function findObjsWithDetails()
    {
        return $this->objRepository->findObjsWithDetails();
    }

    /**
     * @param array $array
     * @return array
     */
    public function findObjArr(array $array): array
    {
        $objArr = [];
        $count = count($array);
        for ($i = 0; $i < $count; $i++) {
            $objArr[$i] = $array[$i];
            $objArr[$i]->subjs = DB::table('subjs')->where('obj_id', $array[$i]->id)->get();
            $countSubj = count($objArr[$i]->subjs);
            for ($j = 0; $j < $countSubj; $j++) {
                $objArr[$i]->subjs[$j]->path = DB::table('img_subj')->where('subj_id', $objArr[$i]->subjs[$j]->id)->pluck('path');
            }
        }

        return $objArr;
    }

    public function findIdObjByUserId()
    {
        return $this->objRepository->findIdObjByUserId();
    }

    public function findObjByUserId()
    {
        return $this->objRepository->findObjByUserId();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findByIdOnlyObj(int $id): mixed
    {
        return $this->objRepository->findByIdOnlyObj($id);
    }
}

