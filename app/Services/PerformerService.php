<?php

namespace App\Services;

use App\Models\CatalogCategory;
use App\Models\Performer;
use App\Repositories\PerformerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformerService
{
    public function __construct(private PerformerRepository $repository)
    {
    }

    public function list(CatalogCategory $category, ?string $city = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getActiveByCategory($category, $city, $perPage);
    }

    /**
     * @param CatalogCategory $category
     * @param int $id
     * @return Model
     */
    public function card(CatalogCategory $category, int $id): Model|Performer|null
    {
        $performer = $this->repository->findActiveInCategory($category->id, $id);

        abort_if($performer === null, 404);

        $performer->increment('views');

        return $performer;
    }

    public function findIndexMetaDescription(CatalogCategory $category, LengthAwarePaginator $performers): string
    {
        $parts = [];

        $city = $this->cityName();
        $parts[] = $category->name . ($city ? ' в ' . $city : '');

        if ($performers->total() > 0) {
            $parts[] = $this->pluralPerformers($performers->total())
                . ' с описаниями, фото, видео и ценами';
        }

        $minPrice = $performers->getCollection()
            ->flatMap(fn($performer) => $performer->packages->pluck('price_min'))
            ->filter()
            ->min();

        if ($minPrice !== null) {
            $parts[] = 'цены от ' . number_format($minPrice, 0, ',', ' ') . ' ₽';
        }

        $meta = implode('. ', $parts) . '.';

        return mb_substr(ucfirst($meta), 0, 250);
    }

    private function cityName(): string
    {
        return (string)session('user_city', '');
    }

    private function pluralPerformers(int $n): string
    {
        return $n . ' ' . trans_choice('исполнитель|исполнителя|исполнителей', $n);
    }

    public function findMetaDescription(CatalogCategory $category, Performer $performer): string
    {
        $parts = [];

        // Основа: что и где
        $parts[] = $performer->name . ' — ' . $category->name
            . ($performer->city?->name ? ', ' . $performer->city->name : '');

        // Короткое описание (без тегов, до ~140 символов)
        $description = trim(strip_tags((string)$performer->description));
        if ($description !== '') {
            $excerpt = mb_substr($description, 0, 140);
            if (mb_strlen($description) > 140) {
                $excerpt .= '…';
            }
            $parts[] = $excerpt;
        }

        // Минимальная цена из пакетов
        $minPrice = $performer->packages
            ->pluck('price_min')
            ->filter()
            ->min();

        if ($minPrice !== null) {
            $parts[] = 'Цены от ' . number_format($minPrice, 0, ',', ' ') . ' ₽';
        }

        return mb_substr(implode('. ', $parts), 0, 250);
    }

    public function create(array $data): Performer
    {
        return $this->repository->create($data);
    }

    public function listForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Performer::query()
            ->where('user_id', $userId)
            ->with(['category', 'avatar'])
            ->orderByDesc('id')
            ->get();
    }

    public function findOwnedByUser(int $id, int $userId): Performer
    {
        $performer = Performer::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        abort_if($performer === null, 404, 'Карточка не найдена');

        return $performer;
    }

    public function publish(Performer $performer): void
    {
        $performer->update(['published' => 1]);
    }

    public function unpublish(Performer $performer): void
    {
        $performer->update(['published' => 0]);
    }

    public function update(Performer $performer, array $data): Performer
    {
        DB::transaction(function () use ($performer, $data) {
            $performer->fill($data);

            $performer->save();
        });

        return $performer->fresh();
    }

    public function listAllForUser(int $userId): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Performer::where('user_id', $userId)
            ->with('firstPhoto') // <-- вот эта строчка делает всю магию: загружает первое фото
            ->orderBy('created_at', 'desc')
            ->paginate(12); // пагинация сразу из коробки
    }


}
