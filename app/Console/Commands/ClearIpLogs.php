<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;
use Throwable;

class ClearIpLogs extends Command
{
    protected $signature = 'reports:clear-ip {--days=7 : Сколько дней хранить}';

    protected $description = 'Удаляет записи IP-логов старше N дней';

    public function __construct(
        private readonly ReportService $reportService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $deleted = $this->reportService->clearDb();

            $this->info("Deleted: {$deleted}");

            return self::SUCCESS;
        } catch (Throwable $e) {
            // clearDb уже логирует причину; здесь только статус для планировщика
            $this->error('Ошибка очистки: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
