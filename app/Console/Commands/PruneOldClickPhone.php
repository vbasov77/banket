<?php

namespace App\Console\Commands;

use App\Models\ClickPhone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneOldClickPhone extends Command
{
    protected $signature = 'click-phone:prune {--days=14 : Сколько дней хранить}';

    protected $description = 'Удаляет статистику кликов по телефону старше N дней';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = ClickPhone::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        if ($deleted > 0) {
            Log::channel('info_file')->info('ClickPhone: очистка статистики', [
                'deleted' => $deleted,
                'older_than' => $cutoff->toDateTimeString(),
            ]);
        }

        $this->info("Deleted: {$deleted} (older than {$cutoff->toDateTimeString()})");

        return self::SUCCESS;
    }
}
