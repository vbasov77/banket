<?php

namespace Database\Seeders;

use App\Models\AlbumsVk;
use Illuminate\Database\Seeder;

class AlbumsVkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AlbumsVk::create([
            'city_id' => 1,
            'city_name' => 'Санкт-Петербург',
            'album_id' => 313725647,
            'group_id' => 236143783
        ]);

        AlbumsVk::create([
            'city_id' => 2,
            'city_name' => 'Москва',
            'album_id' => 311259001,
            'group_id' => 239358548
        ]);

        // Добавьте больше записей по необходимости
    }
}
