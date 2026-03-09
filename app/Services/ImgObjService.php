<?php


namespace App\Services;


use App\Models\ImgObj;
use App\Repositories\ImgObjRepository;
use App\Repositories\KeyRepository;
use Illuminate\Http\Request;
use PharIo\Version\Exception;


class ImgObjService extends Service
{

    private $keyRepository;
    private $imgObjRepository;

    private $vkService;

    public function __construct()
    {
        $this->keyRepository = new KeyRepository();
        $this->vkService = new VkService();
        $this->imgObjRepository = new ImgObjRepository();
    }


    public function imgObjStore(Request $request, int $objId)
    {
        if (!empty($request->file('img'))) {
            $keyGo = $this->keyRepository->idGroupVkMaterial();
            $photo = $this->vkService->createOneImgInVk($request, $keyGo);

            $data = [
                'obj_id' => $objId,
                'path' => $photo->orig_photo->url,
                'photo_id' => $photo->id,
            ];

            $id = ImgObj::insertGetId($data);

            return [$photo->orig_photo->url, $id];
        }
    }

    public function imgObjUpdate(Request $request, int $id)
    {
        if (!empty($request->file('img'))) {
            $keyGo = $this->keyRepository->idGroupVkMaterial();
            $photo = $this->vkService->createOneImgInVk($request, $keyGo);

            $data = [
                'path' => $photo->orig_photo->url,
                'photo_id' => $photo->id,
            ];

            ImgObj::where('id', $id)->update($data);

            return $photo->orig_photo->url;
        }
    }

    public function findImgByObjId(int $id)
    {
        return $this->imgObjRepository->findImgByObjId($id);
    }

}