<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class DeleteOldMessages extends Command
{
    protected $signature = 'messages:delete-old';
    protected $description = 'Удалить сообщения старше 15 дней';

    public function handle(): int
    {
        $days = 15;
        $cutoff = Carbon::now()->subDays($days);

        // Вариант А: простой запрос (если таблица называется messages)
        $deletedCount = DB::table('messages')
            ->where('created_at', '<=', $cutoff)
            ->delete();

        $this->info("Удалено сообщений: {$deletedCount}");

        return Command::SUCCESS;
    }
}
