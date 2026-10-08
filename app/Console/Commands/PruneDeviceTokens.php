<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneDeviceTokens extends Command
{
    // Так команду будут вызывать: php artisan device-tokens:prune
    protected $signature = 'device-tokens:prune';

    protected $description = 'Удаляет device_tokens, по которым не было активности 30 дней (мертвые токены от переустановок)';

    public function handle(): int
    {
        // Ищем токены, у которых last_used_at старше 30 дней (или вообще не заполнялось,
        // но созданы более 30 дней назад — так не потеряем записи, где last_used_at по какой-то причине null)
        $count = DeviceToken::where(function ($q) {
            $q->where('last_used_at', '<', now()->subDays(30))
                ->orWhere(function ($q2) {
                    $q2->whereNull('last_used_at')
                        ->where('created_at', '<', now()->subDays(30));
                });
        })
            ->delete();

        if ($count > 0) {
            Log::channel('info_file')->info(["device-tokens:prune: удалено {$count} мертвых токенов"]);
            $this->info("Удалено токенов: {$count}");
        } else {
            $this->info('Мертвых токенов нет — чистить нечего');
        }

        return self::SUCCESS;
    }
}
