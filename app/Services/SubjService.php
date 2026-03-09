<?php


namespace App\Services;

use App\Models\Obj;
use App\Models\Subj;
use App\Repositories\ObjRepository;
use App\Repositories\SubjRepository;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;


class SubjService extends Service
{
    private $subjRepository;
    private $objRepository;

    public function __construct()
    {
        $this->subjRepository = new SubjRepository();
        $this->objRepository = new ObjRepository();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->subjRepository->findById($id);
    }


    public function findByIdForEdit(int $id)
    {
        return $this->subjRepository->findByIdForEdit($id);
    }
    /**
     * @param int $id
     * @return bool
     */
    public function existsImg(int $id): bool
    {
        return $this->subjRepository->existsImg($id);
    }

    /**
     * @param array $array
     * @param int $id
     * @return void
     */
    public function update(array $array, int $id): void
    {
        $this->subjRepository->update($array, $id);
    }

    public function findMySubjs(int $objId)
    {
        return $this->subjRepository->findMySubjs($objId);
    }

}

