<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Города-миллионники
        $millionaires = [
            'Санкт-Петербург', 'Москва'/*, 'Новосибирск', 'Екатеринбург',
            'Казань', 'Нижний Новгород', 'Челябинск', 'Красноярск',
            'Самара', 'Уфа', 'Ростов‑на‑Дону', 'Омск', 'Краснодар',
            'Пермь', 'Воронеж', 'Волгоград',*/
        ];

        // Пригороды Санкт-Петербурга (как отдельные города в справочнике)
        $spbSuburbs = [
            'Пушкин',
            'Петергоф',
            'Красное Село',
            'Колпино',
            'Сестрорецк',
            'Кронштадт',
            'Ломоносов',
            'Гатчина',
            'Всеволожск',
        ];

        foreach (array_merge($millionaires, $spbSuburbs) as $name) {
            City::firstOrCreate(['name' => $name]);
        }
    }
}
