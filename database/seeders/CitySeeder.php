<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Санкт-Петербург'],
            ['name' => 'Москва'],
        ];

        DB::table('cities')->insert($cities);
    }
}
