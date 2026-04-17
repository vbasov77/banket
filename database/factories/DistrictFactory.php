<?php

namespace Database\Factories;

use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistrictFactory extends Factory
{
    /**
     * Тип фабрики, соответствующий модели.
     */
    protected $model = District::class;

    /**
     * Определите состояние по умолчанию для модели.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->citySuffix() . ' район',
            'city_id' => $this->faker->numberBetween(1, 10)
        ];
    }
}
