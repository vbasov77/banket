<?php

namespace Database\Factories;


use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subj;
use App\Models\Obj;

// добавляем импорт модели Obj

class SubjFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $siteTypes = ['База отдыха', 'Банкетный зал', 'Ресторан', 'Лофт', 'Шатер', 'Терраса'];
        $features = [
            'Можно свои б/а напитки',
            'Выездная регистрация',
            'Музыкальное оборудование',
            'Фотозона',
            'Аниматоры',
            'Ведущий/Тамада'
        ];

        // Создаём связанную модель Obj
        $obj = Obj::factory()->create();
// Создаём или находим существующего пользователя
        $user = User::factory()->create();
        return [
            'obj_id' => $obj->id, // теперь obj_id будет заполнен
            'name_subj' => $this->faker->words(3, true),
            'minimum_cost' => $this->faker->numberBetween(5000, 50000),
            'per_person' => $this->faker->numberBetween(1000, 5000),
            'capacity_to' => $this->faker->numberBetween(20, 200),
            'furshet' => $this->faker->numberBetween(30, 250),
            'site_type' => $this->getRandomSubset($siteTypes, 1, 3),
            'features' => $this->getRandomSubset($features, 1, 4),
            'text_subj' => $this->faker->realText(150),
            'published' => $this->faker->randomElement([0, 1]),
        ];
    }

    private function getRandomSubset(array $array, int $min = 1, int $max = null): array
    {
        if ($max === null) {
            $max = count($array);
        }
        $count = $this->faker->numberBetween($min, min($max, count($array)));
        shuffle($array);
        return array_slice($array, 0, $count);
    }
}
