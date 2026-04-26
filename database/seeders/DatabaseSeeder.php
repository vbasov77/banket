<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(ObjsSeeder::class);
        $this->call(DetailsObjSeeder::class);
        $this->call(SubjsSeeder::class);
        $this->call(ImgSubjSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(DistrictSeeder::class);

    }
}
