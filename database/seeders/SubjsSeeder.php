<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Obj;
use App\Models\Subj;
use Faker\Generator as Faker;

class SubjsSeeder extends Seeder
{
    public function run(): void
    {
        $objs = Obj::all();

        if ($objs->isEmpty()) {
            $this->command->info('Нет объектов для создания субъектов. Сначала создайте объекты.');
            return;
        }

        foreach ($objs as $obj) {
            Subj::factory()
                ->count(rand(1, 4))
                ->create([
                    'obj_id' => $obj->id
                ]);
        }

        $this->command->info('Создано субъектов для ' . $objs->count() . ' объектов');
    }

}
