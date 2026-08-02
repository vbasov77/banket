<?php

// database/seeders/MetroStationsSeeder.php
namespace Database\Seeders;

use App\Models\MetroStation;
use Illuminate\Database\Seeder;

class MetroStationsSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            ['name' => 'Автово', 'city_id' => 1, 'latitude' => 59.8472, 'longitude' => 30.2778],
            ['name' => 'Адмиралтейская', 'city_id' => 1, 'latitude' => 59.9333, 'longitude' => 30.3139],
            ['name' => 'Академическая', 'city_id' => 1, 'latitude' => 60.0111, 'longitude' => 30.3833],
            ['name' => 'Балтийская', 'city_id' => 1, 'latitude' => 59.9028, 'longitude' => 30.2917],
            ['name' => 'Беговая', 'city_id' => 1, 'latitude' => 59.9853, 'longitude' => 30.2114],
            ['name' => 'Бухарестская', 'city_id' => 1, 'latitude' => 59.8833, 'longitude' => 30.3833],
            ['name' => 'Василеостровская', 'city_id' => 1, 'latitude' => 59.9444, 'longitude' => 30.2833],
            ['name' => 'Владимирская', 'city_id' => 1, 'latitude' => 59.9286, 'longitude' => 30.3431],
            ['name' => 'Волковская', 'city_id' => 1, 'latitude' => 59.8972, 'longitude' => 30.3764],
            ['name' => 'Выборгская', 'city_id' => 1, 'latitude' => 59.9792, 'longitude' => 30.3361],
            ['name' => 'Горный институт', 'city_id' => 1, 'latitude' => 59.9286, 'longitude' => 30.2517],
            ['name' => 'Горьковская', 'city_id' => 1, 'latitude' => 59.9556, 'longitude' => 30.3167],
            ['name' => 'Гостиный Двор', 'city_id' => 1, 'latitude' => 59.9375, 'longitude' => 30.3250],
            ['name' => 'Гражданский проспект', 'city_id' => 1, 'latitude' => 60.0333, 'longitude' => 30.4111],
            ['name' => 'Девяткино', 'city_id' => 1, 'latitude' => 60.0556, 'longitude' => 30.4472],
            ['name' => 'Достоевская', 'city_id' => 1, 'latitude' => 59.9306, 'longitude' => 30.3436],
            ['name' => 'Дунайская', 'city_id' => 1, 'latitude' => 59.8528, 'longitude' => 30.4167],
            ['name' => 'Елизаровская', 'city_id' => 1, 'latitude' => 59.9139, 'longitude' => 30.4222],
            ['name' => 'Звенигородская', 'city_id' => 1, 'latitude' => 59.9222, 'longitude' => 30.3417],
            ['name' => 'Звёздная', 'city_id' => 1, 'latitude' => 59.8556, 'longitude' => 30.3389],
            ['name' => 'Кировский Завод', 'city_id' => 1, 'latitude' => 59.8611, 'longitude' => 30.2667],
            ['name' => 'Комендантский проспект', 'city_id' => 1, 'latitude' => 60.0056, 'longitude' => 30.2444],
            ['name' => 'Крестовский остров', 'city_id' => 1, 'latitude' => 59.9722, 'longitude' => 30.2583],
            ['name' => 'Купчино', 'city_id' => 1, 'latitude' => 59.8417, 'longitude' => 30.3500],
            ['name' => 'Ладожская', 'city_id' => 1, 'latitude' => 59.9361, 'longitude' => 30.4389],
            ['name' => 'Ленинский проспект', 'city_id' => 1, 'latitude' => 59.8639, 'longitude' => 30.2639],
            ['name' => 'Лесная', 'city_id' => 1, 'latitude' => 59.9967, 'longitude' => 30.3556],
            ['name' => 'Лиговский проспект', 'city_id' => 1, 'latitude' => 59.9264, 'longitude' => 30.3597],
            ['name' => 'Ломоносовская', 'city_id' => 1, 'latitude' => 59.8889, 'longitude' => 30.4278],
            ['name' => 'Маяковская', 'city_id' => 1, 'latitude' => 59.9306, 'longitude' => 30.3578],
            ['name' => 'Международная', 'city_id' => 1, 'latitude' => 59.8778, 'longitude' => 30.3944],
            ['name' => 'Московская', 'city_id' => 1, 'latitude' => 59.8694, 'longitude' => 30.3278],
            ['name' => 'Московские ворота', 'city_id' => 1, 'latitude' => 59.9056, 'longitude' => 30.3222],
            ['name' => 'Нарвская', 'city_id' => 1, 'latitude' => 59.8806, 'longitude' => 30.2750],
            ['name' => 'Невский проспект', 'city_id' => 1, 'latitude' => 59.9353, 'longitude' => 30.3272],
            ['name' => 'Новочеркасская', 'city_id' => 1, 'latitude' => 59.9278, 'longitude' => 30.4083],
            ['name' => 'Обухово', 'city_id' => 1, 'latitude' => 59.8667, 'longitude' => 30.4528],
            ['name' => 'Обводный канал', 'city_id' => 1, 'latitude' => 59.9147, 'longitude' => 30.3433],
            ['name' => 'Озерки', 'city_id' => 1, 'latitude' => 60.0333, 'longitude' => 30.3444],
            ['name' => 'Парк Победы', 'city_id' => 1, 'latitude' => 59.8861, 'longitude' => 30.3472],
            ['name' => 'Парнас', 'city_id' => 1, 'latitude' => 60.0722, 'longitude' => 30.3194],
            ['name' => 'Петроградская', 'city_id' => 1, 'latitude' => 59.9667, 'longitude' => 30.3083],
            ['name' => 'Пионерская', 'city_id' => 1, 'latitude' => 59.9972, 'longitude' => 30.3111],
            ['name' => 'Площадь Александра Невского-1', 'city_id' => 1, 'latitude' => 59.9250, 'longitude' => 30.3917],
            ['name' => 'Площадь Александра Невского-2', 'city_id' => 1, 'latitude' => 59.9247, 'longitude' => 30.3922],
            ['name' => 'Площадь Восстания', 'city_id' => 1, 'latitude' => 59.9297, 'longitude' => 30.3603],
            ['name' => 'Площадь Ленина', 'city_id' => 1, 'latitude' => 59.9536, 'longitude' => 30.3497],
            ['name' => 'Площадь Мужества', 'city_id' => 1, 'latitude' => 60.0017, 'longitude' => 30.3681],
            ['name' => 'Политехническая', 'city_id' => 1, 'latitude' => 60.0139, 'longitude' => 30.3722],
            ['name' => 'Приморская', 'city_id' => 1, 'latitude' => 59.9583, 'longitude' => 30.2250],
            ['name' => 'Пролетарская', 'city_id' => 1, 'latitude' => 59.8681, 'longitude' => 30.4639],
            ['name' => 'Проспект Большевиков', 'city_id' => 1, 'latitude' => 59.9167, 'longitude' => 30.4806],
            ['name' => 'Проспект Ветеранов', 'city_id' => 1, 'latitude' => 59.8361, 'longitude' => 30.2333],
            ['name' => 'Проспект Просвещения', 'city_id' => 1, 'latitude' => 60.0500, 'longitude' => 30.3306],
            ['name' => 'Проспект Славы', 'city_id' => 1, 'latitude' => 59.8639, 'longitude' => 30.4056],
            ['name' => 'Путиловская', 'city_id' => 1, 'latitude' => 59.8750, 'longitude' => 30.2917],
            ['name' => 'Пушкинская', 'city_id' => 1, 'latitude' => 59.9231, 'longitude' => 30.3356],
            ['name' => 'Рыбацкое', 'city_id' => 1, 'latitude' => 59.8389, 'longitude' => 30.5056],
            ['name' => 'Садовая', 'city_id' => 1, 'latitude' => 59.9269, 'longitude' => 30.3203],
            ['name' => 'Сенная площадь', 'city_id' => 1, 'latitude' => 59.9253, 'longitude' => 30.3125],
            ['name' => 'Спасская', 'city_id' => 1, 'latitude' => 59.9264, 'longitude' => 30.3153],
            ['name' => 'Спортивная', 'city_id' => 1, 'latitude' => 59.9417, 'longitude' => 30.2944],
            ['name' => 'Старая Деревня', 'city_id' => 1, 'latitude' => 59.9889, 'longitude' => 30.2722],
            ['name' => 'Технологический институт-1', 'city_id' => 1, 'latitude' => 59.9247, 'longitude' => 30.3153],
            ['name' => 'Технологический институт-2', 'city_id' => 1, 'latitude' => 59.9244, 'longitude' => 30.3144],
            ['name' => 'Удельная', 'city_id' => 1, 'latitude' => 60.0167, 'longitude' => 30.3556],
            ['name' => 'Улица Дыбенко', 'city_id' => 1, 'latitude' => 59.9083, 'longitude' => 30.4944],
            ['name' => 'Фрунзенская', 'city_id' => 1, 'latitude' => 59.9083, 'longitude' => 30.3056],
            ['name' => 'Чёрная Речка', 'city_id' => 1, 'latitude' => 59.9833, 'longitude' => 30.3000],
            ['name' => 'Чернышевская', 'city_id' => 1, 'latitude' => 59.9431, 'longitude' => 30.3547],
            ['name' => 'Чкаловская', 'city_id' => 1, 'latitude' => 59.9472, 'longitude' => 30.3028],
            ['name' => 'Шушары', 'city_id' => 1, 'latitude' => 59.8389, 'longitude' => 30.4278],
            ['name' => 'Электросила', 'city_id' => 1, 'latitude' => 59.8944, 'longitude' => 30.3361],
            ['name' => 'Юго-Западная', 'city_id' => 1, 'latitude' => 59.8444, 'longitude' => 30.2167],
            ['name' => 'Зенит', 'city_id' => 1, 'latitude' => 59.9725, 'longitude' => 30.2039],
        ];

        foreach ($stations as $s) {
            MetroStation::updateOrCreate(
                ['name' => $s['name']],
                [
                    'city_id'   => $s['city_id'],
                    'latitude'  => $s['latitude'],
                    'longitude' => $s['longitude'],
                ]
            );
        }
    }
}
