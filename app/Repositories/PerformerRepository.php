<?php

namespace App\Repositories;

use App\Models\CatalogCategory;
use App\Models\Performer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class PerformerRepository
{
    public function getActiveByCategory(CatalogCategory $category, ?string $city = null, int $perPage = 20): LengthAwarePaginator
    {
        return Performer::query()
            ->where('category_id', $category->id)
            ->where('published', 1)
            ->with([
                'photos',
                'avatar',
                'packages' => fn ($q) => $q->select('performer_id', 'price_min', 'price_max', 'is_addon', 'sort', 'id'),
            ])
            ->when($city, fn ($q, $city) => $q->where('city_id', $city))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @param int $categoryId
     * @param int $id
     * @return Model|Performer|null
     */
    public function findActiveInCategory(int $categoryId, int $id): Model|Performer|null
    {
        return Performer::query()
            ->where('category_id', $categoryId)
            ->where('id', $id)
            ->with(['category', 'photos', 'avatar', 'user', 'packages'])
            ->first();
    }

    public function create(array $data): Performer
    {
        return Performer::create($data);
    }
}
