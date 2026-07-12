<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Сначала убедимся, что роли существуют (на случай, если запустили только UserSeeder)
        $roles = [
            'admin',
            'soon_banquet',
            'restaurateur',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Получаем ID роли администратора
        $adminRole = Role::where('name', 'admin')->first();
        $banquetRole = Role::where('name', 'soon_banquet')->first();

        if (!$adminRole || !$banquetRole) {
            throw new \RuntimeException('Роли не найдены и не смогли быть созданы. Проверьте миграцию таблицы roles.');
        }

        // Основной администратор
        User::create([
            'name' => 'Администратор',
            'email' => '0120912@mail.ru',
            // Оставил твой хеш. Если хочешь задать простой пароль для тестов, раскомментируй строку ниже и удали строку выше:
            // 'password' => Hash::make('password'),
            'password' => '$2y$12$hW10/n1CyJjAFvzvwTVes.YtSJalQCPB.NJ.U7VmWUXw8M7XlkFae',
            'role_id' => $adminRole->id, // Присваиваем роль вместо флага is_admin
        ]);

//        // Обычный пользователь (например, клиент "Скоро банкет")
//        User::create([
//            'name' => 'Пользователь',
//            'email' => '0120912@bk.ru',
//            'password' => '$2y$12$hW10/n1CyJjAFvzvwTVes.YtSJalQCPB.NJ.U7VmWUXw8M7XlkFae',
//            'role_id' => $banquetRole->id,
//        ]);
//
//        // Несколько случайных пользователей через фабрику
//        // ВАЖНО: В фабрике UserFactory тоже нужно убрать 'is_admin' и добавить логику для role_id,
//        // иначе фабрика будет падать или создавать пользователей без роли.
//        User::factory()->count(10)->create();
    }
}
