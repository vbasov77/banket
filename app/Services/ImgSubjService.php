<?php


namespace App\Services;


use App\Models\ImgObj;
use App\Models\ImgSubj;
use App\Repositories\ImgSubjRepository;
use App\Repositories\KeyRepository;
use Illuminate\Http\Request;


class ImgSubjService extends Service
{

    private $keyRepository;
    private $imgSubjRepository;

    private $vkService;

    public function __construct()
    {
        $this->keyRepository = new KeyRepository();
        $this->vkService = new VkService();
        $this->imgSubjRepository = new ImgSubjRepository();
    }



    public function ImgSubjStore(Request $request, int $id)
    {

        if (!empty($request->file('img'))) {

            $keyGo = $this->keyRepository->idGroupVkBanquet();
            $photo = $this->vkService->createOneImgInVk($request, $keyGo);

            $data = [
                'subj_id' => $id,
                'path' => $photo->orig_photo->url,
                'photo_id' => $photo->id,
                'position' => ImgSubj::where('subj_id', $id)->count() + 1,
            ];
            $id = ImgSubj::insertGetId($data);

            return [$photo->orig_photo->url, $id];
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findImgByObjId(int $id)
    {
        return $this->imgSubjRepository->findImgByObjId($id);
    }

}