<?php

namespace Tests\Feature\City;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCitiesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест успешного получения списка городов
     */
    public function testGetCitiesSuccessfully(): void
    {
        City::query()->delete();

        // Создаём тестовые данные
        City::factory()->create(['name' => 'Москва']);
        City::factory()->create(['name' => 'Санкт-Петербург']);
        City::factory()->create(['name' => 'Екатеринбург']);

        // Отправляем GET‑запрос к маршруту /get-cities
        $response = $this->get('/get-cities');

        // Проверяем статус ответа (должен быть 200)
        $response->assertStatus(200);

        // Проверяем структуру JSON‑ответа (учитываем вашу миграцию — только id и name)
        $response->assertJsonStructure([
            'cities' => [
                '*' => [
                    'id',
                    'name'
                ]
            ]
        ]);

        // Проверяем количество городов в ответе
        $response->assertJsonCount(3, 'cities');

        // Проверяем наличие конкретных городов в ответе
        $response->assertJsonFragment(['name' => 'Москва']);
        $response->assertJsonFragment(['name' => 'Санкт-Петербург']);
        $response->assertJsonFragment(['name' => 'Екатеринбург']);
    }

    /**
     * Тест получения пустого списка городов (когда нет данных в БД)
     */
    public function testGetEmptyCitiesList(): void
    {
        City::query()->delete();

        // База данных пуста (благодаря RefreshDatabase)

        $response = $this->get('/get-cities');

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем, что массив городов пуст
        $response->assertJson(['cities' => []]);

        // Альтернативно: проверяем количество элементов
        $response->assertJsonCount(0, 'cities');
    }
}
