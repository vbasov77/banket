<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Subj;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteSubjFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'subj_id' => Subj::inRandomOrder()->first()?->id ?? Subj::factory()->create()->id,
        ];
    }

}
