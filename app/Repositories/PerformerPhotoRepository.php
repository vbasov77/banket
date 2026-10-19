<?php

namespace App\Repositories;

use App\Models\Performer;
use App\Models\PerformerPhoto;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PerformerPhotoRepository
{
    public function getNextPosition(int $performerId): int
    {
        return PerformerPhoto::where('performer_id', $performerId)->count() + 1;
    }

    public function create(array $data): PerformerPhoto
    {
        return PerformerPhoto::create($data);
    }

    public function findForPerformer(int $photoId, int $performerId): ?PerformerPhoto
    {
        return PerformerPhoto::query()
            ->where('id', $photoId)
            ->where('performer_id', $performerId)
            ->first();
    }

    public function delete(PerformerPhoto $photo): void
    {
        $photo->delete();
    }

    public function findOwnedByUser(int $performerId, int $userId): Performer
    {
        $performer = Performer::query()
            ->where('id', $performerId)
            ->where('user_id', $userId)
            ->first();

        if ($performer === null) {
            throw new ModelNotFoundException('Карточка не найдена');
        }

        return $performer;
    }

    public function updateOrder(array $ids): void
    {
        foreach ($ids as $position => $photoId) {
            PerformerPhoto::where('id', $photoId)
                ->update(['position' => $position + 1]);
        }
    }
}
