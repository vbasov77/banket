<?php

namespace Tests\Feature\AddressSubj;

use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchDistrictsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itValidatesRequiredParameters(): void
    {
        // Тест без параметра q
        $response = $this->get('/api/districts?city_data_city_id=1');
        $response->assertStatus(400);
        $response->assertJson(['error' => 'Запрос обязателен, минимум 2 символа']);

        // Тест с коротким запросом (1 символ)
        $response = $this->get('/api/districts?city_data_city_id=1&q=A');
        $response->assertStatus(400);
        $response->assertJson(['error' => 'Запрос обязателен, минимум 2 символа']);
    }


    /**
     * @test
     */
    public function itSuccessfullySearchesDistricts(): void
    {
        // Создаём город — это гарантирует, что city_id существует
        $city = City::factory()->create(['name' => 'Москва']);

        // Теперь создаём районы, ссылающиеся на созданный город
        District::factory()->create([
            'name' => 'Центральный район',
            'city_id' => $city->id
        ]);
        District::factory()->create([
            'name' => 'Северный район',
            'city_id' => $city->id
        ]);

        // Выполняем запрос с реальным ID города
        $response = $this->get("/api/districts?city_data_city_id={$city->id}&q=район");

        // Проверяем статус
        $response->assertStatus(200);

        // Проверяем количество результатов
        $response->assertJsonCount(2);

        // Проверяем наличие конкретных районов
        $response->assertJsonFragment(['name' => 'Центральный район']);
        $response->assertJsonFragment(['name' => 'Северный район']);

        // Проверяем структуру ответа
        $response->assertJsonStructure([
            [
                'id',
                'name'
            ]
        ]);
    }

    /**
     * @test
     */
    public function itHandlesEmptyResults(): void
    {
        // В базе нет районов с таким названием
        $response = $this->get('/api/districts?city_data_city_id=1&q=ВымышленныйРайон');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Города не найдены']);
    }


    /**
     * @test
     */
    public function itLimitsResultsToTen(): void
    {
        // Сначала создаём город
        $city = City::factory()->create(['name' => 'Москва']);


        // Создаём 15 районов с одинаковым именем, привязанных к созданному городу
        District::factory()
            ->count(15)
            ->create([
                'name' => 'Тестовый район',
                'city_id' => $city->id
            ]);

        // Выполняем запрос с ID созданного города
        $response = $this->get("/api/districts?city_data_city_id={$city->id}&q=Тестовый");

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем, что вернулись только первые 10 записей (ограничение)
        $response->assertJsonCount(10);
    }


    /**
     * @test
     */
    public function itSortsResultsByName(): void
    {
        // Сначала создаём город
        $city = City::factory()->create(['name' => 'Москва']);

        // Создаём районы с разными именами для проверки сортировки
        District::factory()->create([
            'name' => 'Б район',
            'city_id' => $city->id
        ]);
        District::factory()->create([
            'name' => 'А район',
            'city_id' => $city->id
        ]);
        District::factory()->create([
            'name' => 'В район',
            'city_id' => $city->id
        ]);

        // Выполняем запрос с ID созданного города
        $response = $this->get("/api/districts?city_data_city_id={$city->id}&q=район");

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Получаем данные из ответа
        $districts = $response->json();

        // Проверяем, что результаты отсортированы по имени (А → Б → В)
        $this->assertEquals('А район', $districts[0]['name']);
        $this->assertEquals('Б район', $districts[1]['name']);
        $this->assertEquals('В район', $districts[2]['name']);

        // Альтернативный способ проверки сортировки — через сравнение с отсортированным массивом
        $expectedNames = ['А район', 'Б район', 'В район'];
        $actualNames = array_column($districts, 'name');
        $this->assertEquals($expectedNames, $actualNames);
    }


    /**
     * @test
     */
    public function itFiltersByCityId(): void
    {
        $city1 = City::factory()->create(['name' => 'Москва']);
        $city2 = City::factory()->create(['name' => 'Санкт-Петербург']);

        District::factory()->create([
            'name' => 'Район города 1',
            'city_id' => $city1->id
        ]);
        District::factory()->create([
            'name' => 'Другой район города 1',
            'city_id' => $city1->id
        ]);
        District::factory()->create([
            'name' => 'Район города 2',
            'city_id' => $city2->id
        ]);

        // Передаём параметры в URL — это надёжнее для GET‑запросов
        $response = $this->get("/api/districts?city_data_city_id={$city1->id}&q=район");

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => 'Район города 1']);
        $response->assertJsonFragment(['name' => 'Другой район города 1']);
        $response->assertDontSee('Район города 2');
    }
}
