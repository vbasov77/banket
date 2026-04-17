<?php

namespace Database\Seeders;


use App\Models\User;
use App\Models\Obj;
use Illuminate\Database\Seeder;

class ObjsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $user = User::factory()->create();
            $users = collect([$user]);
        }

        foreach ($users as $user) {
            Obj::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
