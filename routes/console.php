<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('messages:delete-old')->daily()->timezone('Europe/Moscow');
Schedule::command('sitemap:generate')->daily();
Schedule::command('click-phone:prune')->dailyAt('04:00')->timezone('Europe/Moscow');
Schedule::command('reports:clear-ip')->dailyAt('04:10')->timezone('Europe/Moscow');
Schedule::command('device-tokens:prune')->dailyAt('04:20')->timezone('Europe/Moscow');
