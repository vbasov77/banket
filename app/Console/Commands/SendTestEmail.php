<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Тестовая отправка письма на 0120912@mail.ru';

    public function handle(): int
    {
        Mail::raw('привет', function ($message) {
            $message->to('0120912@mail.ru')
                ->subject('Тест с сайта FeastBoom');
        });

        $this->info('Письмо отправлено на 0120912@mail.ru');

        return Command::SUCCESS;
    }
}
