<?php

namespace Database\Factories;

use App\Models\Obj;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Generator as Faker;

/**
 * @extends Factory<Obj>
 */
class ObjFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Создаём или находим существующего пользователя
        $user = User::factory()->create();
        return [
            'user_id' => $user->id, // заполняем user_id существующим ID
            'name_obj' => $this->faker->company(),
            'phone_obj' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * State for test objects
     */
    public function testObject(): static
    {
        return $this->state([
            'name_obj' => 'Тестовый объект',
            'phone_obj' => '+7 (999) 123-45-67',
        ]);
    }

    /**
     * State with specific name
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name_obj' => $name,
        ]);
    }

    /**
     * State with specific phone
     */
    public function withPhone(string $phone): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_obj' => $phone,
        ]);
    }
}
