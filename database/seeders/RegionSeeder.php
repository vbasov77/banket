<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $spb = Region::firstOrCreate(['name' => 'Санкт-Петербург']);
        $lo = Region::firstOrCreate(['name' => 'Ленинградская область']);

        City::where('name', 'Санкт-Петербург')->update(['region_id' => $spb->id]);

        // Все остальные города, у которых регион не задан, считаем ЛО
        City::whereNull('region_id')->update(['region_id' => $lo->id]);
    }
}