<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use Illuminate\Database\Seeder;

class CatalogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'fotografi',  'name' => 'Фотографы',        'position' => 1],
            ['slug' => 'videografy', 'name' => 'Видеографы',       'position' => 2],
            ['slug' => 'vedushchie', 'name' => 'Ведущие',          'position' => 3],
            ['slug' => 'dj',         'name' => 'DJ',               'position' => 4],
            ['slug' => 'oformlenie', 'name' => 'Оформление залов', 'position' => 5],
        ];

        foreach ($categories as $category) {
            CatalogCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
