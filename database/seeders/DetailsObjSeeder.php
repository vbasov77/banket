<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Obj;
use App\Models\DetailsObj;
use Faker\Generator as Faker;

class DetailsObjSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаём экземпляр Faker с русской локалью
        $faker = \Faker\Factory::create('ru_RU');

        $objs = Obj::all();

        if ($objs->isEmpty()) {
            $this->command->info('Нет объектов для добавления деталей. Сначала создайте объекты.');
            return;
        }

        // Варианты для полей
        $eventTypes = ['Свадьба', 'День рождения', 'Выпускной', 'Корпоратив', 'Юбилей', 'Фуршет'];
        $kitchenTypes = ['Русская', 'Кавказская', 'Европейская', 'Азиатская'];
        $serviceTypes = ['Ведущий/Тамада', 'Диджей', 'Живая музыка', 'Фотограф/Видеооператор', 'Аниматоры', 'Украшение зала'];
        $paymentMethods = ['Наличные', 'Карта', 'Перевод'];

        foreach ($objs as $obj) {
            // Случайное количество мероприятий (1–4)
            $selectedEvents = $this->getRandomSubset($eventTypes, 1, 4);
            // Случайное количество кухонь (1–3)
            $selectedKitchens = $this->getRandomSubset($kitchenTypes, 1, 3);
            // Случайное количество услуг (1–3)
            $selectedServices = $this->getRandomSubset($serviceTypes, 1, 3);
            // Случайное количество способов оплаты (1–2)
            $selectedPayments = $this->getRandomSubset($paymentMethods, 1, 2);

            DetailsObj::create([
                'obj_id' => $obj->id,
                'for_events' => $selectedEvents,
                'kitchen' => $selectedKitchens,
                'service' => $selectedServices,
                'alcohol' => '2:5',
                'more' => '2:5',
                'payment_methods' => $selectedPayments,
                'text_obj' => 'Прекрасный банкетный зал с панорамными окнами и профессиональным обслуживанием. Идеально подходит для ' .
                    implode(', ', $selectedEvents) . '.',
            ]);
        }

        $this->command->info('Добавлены детали для ' . $objs->count() . ' объектов');
    }

    /**
     * Получить случайное подмножество элементов из массива
     *
     * @param array $array Исходный массив
     * @param int $min Минимальное количество элементов
     * @param int $max Максимальное количество элементов
     * @return array Случайное подмножество
     */
    private function getRandomSubset(array $array, int $min = 1, int $max = null): array
    {
        if ($max === null) {
            $max = count($array);
        }

        $count = rand($min, min($max, count($array)));
        shuffle($array);
        return array_slice($array, 0, $count);
    }
}
