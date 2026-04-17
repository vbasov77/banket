<?php

namespace Database\Factories;

use App\Models\Subj;
use App\Models\ImgSubj;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImgSubjFactory extends Factory
{
    public function definition(): array
    {
        // Создаём связанную модель Subj
        $subj = Subj::factory()->create();

        return [
            'subj_id' => $subj->id, // теперь subj_id будет заполнен
            'photo_id' => $this->faker->numberBetween(1, 100),
            'path' => $this->faker->imageUrl(),
            'position' => $this->faker->numberBetween(1, 10),
        ];
    }
}
