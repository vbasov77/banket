<?php

namespace Tests\Feature\AddressSubj;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CitySearchTest extends TestCase
{
    use RefreshDatabase; // Используем RefreshDatabase вместо DatabaseMigrations

    protected function setUp(): void
    {
        parent::setUp();

        // Удаляем колонку point, если существует (до миграций)
        if (Schema::hasTable('group_address_objs') && Schema::hasColumn('group_address_objs', 'location')) {
            Schema::table('group_address_objs', function ($table) {
                $table->dropColumn('location');
            });
        }

        // Создаём тестовые данные после миграций
        $this->createTestCities();
    }

    /**
     * Вспомогательный метод для создания тестовых городов
     */
    private function createTestCities(): void
    {
        City::create(['name' => 'Москва']);
        City::create(['name' => 'Московская область']);
        City::create(['name' => 'Санкт‑Петербург']);
        City::create(['name' => 'Екатеринбург']);
        City::create(['name' => 'Нижний Новгород']);
    }

    /**
     * Тест успешного поиска с результатами
     */
    public function testSearchSuccessWithResults(): void
    {
        $response = $this->get('/api/cities?q=Моск');

        $response->assertStatus(200);
        $response->assertJsonCount(2); // Ожидаем 2 города: Москва, Московская область
        $response->assertJsonFragment(['name' => 'Москва']);
        $response->assertJsonFragment(['name' => 'Московская область']);

        // Проверяем сортировку по имени
        $data = $response->json();
        $this->assertEquals('Москва', $data[0]['name']);
        $this->assertEquals('Московская область', $data[1]['name']);
    }

    /**
     * Тест поиска без результатов
     */
    public function testSearchNoResults(): void
    {
        $response = $this->get('/api/cities?q=ВыдуманныйГород');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Города не найдены']);
    }

    /**
     * Тест слишком короткого запроса (меньше 2 символов)
     */
    public function testSearchShortQuery(): void
    {
        $response = $this->get('/api/cities?q=A');

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Запрос слишком короткий']);
    }

    /**
     * Тест пустого запроса
     */
    public function testSearchEmptyQuery(): void
    {
        $response = $this->get('/api/cities?q=');

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Запрос слишком короткий']);
    }

    /**
     * Тест ограничения количества результатов (не больше 10)
     */
    public function testSearchResultsLimit(): void
    {
        // Очищаем таблицу перед созданием большого количества записей
        DB::table('cities')->delete(); // Вместо truncate()

        // Создаём много городов с одинаковым началом имени
        for ($i = 1; $i <= 15; $i++) {
            City::create(['name' => "Город $i"]);
        }

        $response = $this->get('/api/cities?q=Город');

        $response->assertStatus(200);
        $response->assertJsonCount(10); // Должен вернуть только 10 из 15
    }

    /**
     * Тест регистронезависимого поиска
     */
    public function testSearchCaseInsensitive(): void
    {
        $response1 = $this->get('/api/cities?q=москва');
        $response2 = $this->get('/api/cities?q=МОСКВА');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $this->assertEquals(
            $response1->json(),
            $response2->json()
        );
    }
}
