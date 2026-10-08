<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('messages:delete-old')->daily()->timezone('Europe/Moscow');
        $schedule->command('sitemap:generate')->daily();
        $schedule->command('click-phone:prune')->dailyAt('04:00')->timezone('Europe/Moscow');
        $schedule->command('reports:clear-ip')->dailyAt('04:10')->timezone('Europe/Moscow');
        $schedule->command('device-tokens:prune')->dailyAt('04:20')->timezone('Europe/Moscow');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
