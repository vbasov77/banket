<?php

namespace App\Repositories;

use App\Models\PerformerPackage;
use Illuminate\Support\Collection;

class PerformerPackageRepository
{
    /**
     * Пакеты исполнителя в порядке вывода.
     */
    public function getForPerformer(int $performerId): Collection
    {
        return PerformerPackage::query()
            ->where('performer_id', $performerId)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    /**
     * Перезапись всех пакетов исполнителя (replace-паттерн).
     * Возвращает минимальную цену для денормализации.
     */
    public function syncForPerformer(int $performerId, array $packages): ?int
    {
        PerformerPackage::query()->where('performer_id', $performerId)->delete();

        if ($packages === []) {
            return null;
        }

        foreach ($packages as $i => $package) {
            PerformerPackage::query()->create([
                'performer_id' => $performerId,
                'title'        => $package['title'],
                'description'  => $package['description'] ?? null,
                'price_min'    => $package['price_min'] ?? null,
                'price_max'    => $package['price_max'] ?? null,
                'is_addon'     => $package['is_addon'] ?? false,
                'sort'         => $i,
            ]);
        }

        $min = PerformerPackage::query()
            ->where('performer_id', $performerId)
            ->whereNotNull('price_min')
            ->min('price_min');

        return $min !== null ? (int)$min : null;
    }
}
