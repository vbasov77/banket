<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Zags;
use Illuminate\Database\Seeder;

class ZagsSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем города (они должны быть в базе!)
        $spb = City::where('name', 'Санкт-Петербург')->firstOrFail();
        $pushkin = City::where('name', 'Пушкин')->firstOrFail();
        $peterhof = City::where('name', 'Петергоф')->firstOrFail();
        $kronshtadt = City::where('name', 'Кронштадт')->firstOrFail();

        $zagsData = [
            [
                'name' => 'Дворец бракосочетания №1',
                'address' => 'Английская набережная, 28, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9297,
                'longitude' => 30.2989,
            ],
            [
                'name' => 'Дворец бракосочетания №2',
                'address' => 'Фурштатская улица, 52, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9403,
                'longitude' => 30.3497,
            ],
            // остальные ЗАГСы... (список тот же, что был выше)
            [
                'name' => 'Отдел ЗАГС Адмиралтейского района',
                'address' => 'ул. Садовая, 48, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9245,
                'longitude' => 30.3210,
            ],
            [
                'name' => 'Отдел ЗАГС Василеостровского района',
                'address' => 'Большой проспект В.О., 61, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9380,
                'longitude' => 30.2650,
            ],
            [
                'name' => 'Отдел ЗАГС Выборгского района',
                'address' => 'проспект Энгельса, 103, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 60.0060,
                'longitude' => 30.3150,
            ],
            [
                'name' => 'Отдел ЗАГС Калининского района',
                'address' => 'улица Верности, 4, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 60.0033,
                'longitude' => 30.4028,
            ],
            [
                'name' => 'Отдел ЗАГС Красногвардейского района',
                'address' => 'Большеохтинский проспект, 11, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9586,
                'longitude' => 30.4136,
            ],
            [
                'name' => 'Отдел ЗАГС Красносельского района',
                'address' => 'проспект Ветеранов, 69, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.8453,
                'longitude' => 30.2417,
            ],
            [
                'name' => 'Отдел ЗАГС Кронштадтского района',
                'address' => 'г. Кронштадт, проспект Ленина, 36',
                'city_id' => $spb->id,
                'latitude' => 59.9917,
                'longitude' => 29.7783,
            ],
            [
                'name' => 'Отдел ЗАГС Курортного района',
                'address' => 'г. Сестрорецк, улица Токарева, 7',
                'city_id' => $spb->id,
                'latitude' => 60.0933,
                'longitude' => 29.9667,
            ],
            [
                'name' => 'Отдел ЗАГС Московского района',
                'address' => 'Московский проспект, 194, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.8514,
                'longitude' => 30.3089,
            ],
            [
                'name' => 'Отдел ЗАГС Невского района',
                'address' => 'проспект Большевиков, 2, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9086,
                'longitude' => 30.4639,
            ],
            [
                'name' => 'Отдел ЗАГС Петроградского района',
                'address' => 'Большая Монетная улица, 17–19, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9600,
                'longitude' => 30.3189,
            ],
            [
                'name' => 'Отдел ЗАГС Приморского района',
                'address' => 'Стародеревенская улица, 5, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9886,
                'longitude' => 30.2581,
            ],
            [
                'name' => 'Отдел ЗАГС Фрунзенского района',
                'address' => 'проспект Славы, 31, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.8614,
                'longitude' => 30.3875,
            ],
            [
                'name' => 'Отдел ЗАГС Центрального района',
                'address' => 'Суворовский проспект, 41, Санкт-Петербург',
                'city_id' => $spb->id,
                'latitude' => 59.9431,
                'longitude' => 30.3772,
            ],
            [
                'name' => 'Отдел ЗАГС Пушкинского района',
                'address' => 'г. Пушкин, ул. Садовая, 22',
                'city_id' => $pushkin->id,
                'latitude' => 59.7189,
                'longitude' => 30.4123,
            ],
            [
                'name' => 'Отдел ЗАГС Петродворцового района',
                'address' => 'г. Петергоф, Торговая площадь, 5',
                'city_id' => $peterhof->id,
                'latitude' => 59.8833,
                'longitude' => 29.9167,
            ],
        ];

        foreach ($zagsData as $data) {
            Zags::updateOrInsert(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
