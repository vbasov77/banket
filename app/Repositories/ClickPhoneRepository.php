<?php

namespace App\Repositories;

use App\Models\ClickPhone;
use Illuminate\Support\Collection;

class ClickPhoneRepository
{
    /**
     * Количество кликов по телефону для субъекта.
     * @param int $subjId
     * @return int
     */
    public function countForSubj(int $subjId): int
    {
        return ClickPhone::where('subj_id', $subjId)->count();
    }

    /**
     * Статистика по дням за период (удобно для графиков).
     * @param int $subjId
     * @param int $days
     * @return array
     */
    public function countByDay(int $subjId, int $days = 30): array
    {
        return ClickPhone::selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->where('subj_id', $subjId)
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('cnt', 'day')
            ->toArray();
    }

    /**
     * @param object $startDate
     * @param object $endDate
     * @return Collection
     */
    public function findPhoneClicksByDay(object $startDate, object $endDate): Collection
    {
        return ClickPhone::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->pluck('cnt', 'day');
    }

    /**
     * Количество кликов за последние N часов (антифлуд).
     * @param int $subjId
     * @param int $userId
     * @param int $minutes
     * @return int
     */
    public function recentCount(int $subjId, int $userId, int $minutes = 30): int
    {
        return ClickPhone::where('subj_id', $subjId)
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Кликал ли этот IP по данному subj сегодня.
     * @param int $subjId
     * @param string $ip
     * @return bool
     */
    public function existsToday(int $subjId, string $ip): bool
    {
        return ClickPhone::where('subj_id', $subjId)
            ->where('ip', $ip)
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }

}
