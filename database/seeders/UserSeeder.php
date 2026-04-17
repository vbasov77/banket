<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Основной администратор
        User::create([
            'name' => 'Администратор',
            'email' => '0120912@mail.ru',
            'password' => '$2y$12$hW10/n1CyJjAFvzvwTVes.YtSJalQCPB.NJ.U7VmWUXw8M7XlkFae',
            'is_admin' => true,
        ]);

        // Обычный пользователь
        User::create([
            'name' => 'Пользователь',
            'email' => '0120912@bk.ru',
            'password' => '$2y$12$hW10/n1CyJjAFvzvwTVes.YtSJalQCPB.NJ.U7VmWUXw8M7XlkFae',
            'is_admin' => false,
        ]);

        // Несколько случайных пользователей через фабрику
        User::factory()->count(10)->create();
    }
}
