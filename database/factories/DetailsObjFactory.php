<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DetailsObj;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailsObj>
 */
class DetailsObjFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $eventTypes = ['Свадьба', 'День рождения', 'Выпускной', 'Корпоратив', 'Юбилей', 'Фуршет'];
        $kitchenTypes = ['Русская', 'Кавказская', 'Европейская', 'Азиатская'];
        $serviceTypes = ['Ведущий/Тамада', 'Диджей', 'Живая музыка', 'Фотограф/Видеооператор', 'Аниматоры', 'Украшение зала'];
        $paymentMethods = ['Наличные', 'Карта', 'Перевод'];

        return [
            'for_events' => $this->getRandomSubset($eventTypes, 1, 4),
            'kitchen' => $this->getRandomSubset($kitchenTypes, 1, 3),
            'service' => $this->getRandomSubset($serviceTypes, 1, 3),
            'alcohol' => '2:5',
            'more' => '2:5',
            'payment_methods' => $this->getRandomSubset($paymentMethods, 1, 2),
            'text_obj' => $this->faker->realText(200),
        ];
    }

    /**
     * Получить случайное подмножество элементов из массива
     *
     * @param array $array Исходный массив вариантов
     * @param int $min Минимальное количество элементов (по умолчанию 1)
     * @param int $max Максимальное количество элементов
     * @return array Случайное подмножество элементов
     */
    private function getRandomSubset(array $array, int $min = 1, int $max = null): array
    {
        if ($max === null) {
            $max = count($array);
        }

        $count = $this->faker->numberBetween($min, min($max, count($array)));
        $shuffled = $array;
        shuffle($shuffled);
        return array_slice($shuffled, 0, $count);
    }
}
