<?php

namespace Database\Factories;

use App\Models\AddressSubj;
use App\Models\Subj;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressSubjFactory extends Factory
{
    protected $model = AddressSubj::class;

    /**
     * Состояние «привязать к конкретному Subj» — чтобы работало $factory->forSubj($subj)
     */
    public function forSubj(Subj $subj): static
    {
        return $this->state([
            'subj_id' => $subj->id,
        ]);
    }

    public function definition(): array
    {
        return [
            // subj_id не ставим здесь: в тестах будем использовать ->forSubj()

            'group_id'    => rand(1, 10),      // лучше заменить на GroupAddressObj::factory()
            'city_id'     => rand(1, 50),      // аналогично City::factory()
            'district_id' => rand(1, 20),      // District::factory()

            'address'     => [
                'street'  => 'ул. Примерная',
                'house'   => '10',
                'building'=> 'А',
                'flat'    => '5',
            ],

            'longitude'   => 30.315868 + (mt_rand(-1000, 1000) / 1_000_000),
            'latitude'    => 59.939767 + (mt_rand(-1000, 1000) / 1_000_000),
        ];
    }
}
