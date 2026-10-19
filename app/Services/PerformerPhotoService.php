<?php

namespace App\Services;

use App\Models\Performer;
use App\Models\PerformerPhoto;
use App\Repositories\ImgBanRepository;
use App\Repositories\PerformerPhotoRepository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformerPhotoService
{
    public function __construct(
        private readonly PerformerPhotoRepository $repository,
        private readonly ImgBanSubjService $imageban,
        private readonly ImgBanRepository $imgBanRepository,

    ) {}

    public function store(Request $request, Performer $performer): array
    {
        // Два размера — как у субъектов: 900 для галереи, 360 для превью
        [$bigId, $bigImg]   = $this->imageban->createInImgBan($request, 900);
        [$smallId, $smallImg] = $this->imageban->createInImgBan($request, 360);

        $photo = $this->repository->create([
            'performer_id' => $performer->id,
            'big_id'       => $bigId,
            'big_img'      => $bigImg,
            'small_id'     => $smallId,
            'small_img'    => $smallImg,
            'position'     => $this->repository->getNextPosition($performer->id),
        ]);

        return ['path' => $smallImg, 'id' => $photo->id];
    }

    public function findOwnedByUser(int $performerId, int $userId): Performer
    {
        return $this->repository->findOwnedByUser($performerId, $userId);
    }

    public function delete(PerformerPhoto $photo): void
    {
        $this->imgBanRepository->delete($photo->big_id);
        $this->imgBanRepository->delete($photo->small_id);

        $this->repository->delete($photo);
    }

    public function changeOrder(array $ids): void
    {
        $this->repository->updateOrder($ids);
    }
}
