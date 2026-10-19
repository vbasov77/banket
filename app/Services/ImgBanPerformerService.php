<?php


namespace App\Services;


use App\Models\ImgBanSubj;
use App\Models\ImgObj;
use App\Models\Performer;
use App\Models\PerformerAvatar;
use App\Models\PerformerPhoto;
use App\Repositories\ImgBanRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImgBanPerformerService extends Service
{
    protected ImgBanRepository $imgBanRepository;

    /**
     * @param ImgBanRepository $imgBanRepository
     */
    public function __construct(ImgBanRepository $imgBanRepository)
    {
        $this->imgBanRepository = $imgBanRepository;
    }


    /**
     * Удаление всех фото performer через API imageban
     * @param int $performerId
     * @return void
     */
    public function destroyPerformerImages(int $performerId): void
    {
        $ids = collect();

//        $ids->push(
//            PerformerAvatar::where('performer_id', $performerId)
//                ->whereNotNull('photo_id')
//                ->pluck('photo_id')
//        );

        // big_id из img_ban_subj — большие фото performer
        $ids->push(
            PerformerPhoto::whereNotNull('big_id')
                ->pluck('big_id')
        );

        // small_id из img_ban_subj — малые фото performer
        $ids->push(
            PerformerPhoto::whereNotNull('small_id')
                ->pluck('small_id')
        );

        $photoIds = $ids->flatten()->filter()->unique()->toArray();

        $this->imgBanRepository->cycleDel($photoIds);
    }

}
