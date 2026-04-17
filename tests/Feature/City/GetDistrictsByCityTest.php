<?php

namespace Tests\Feature\City;

use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class GetDistrictsByCityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Session::start();
    }

    /**
     * Тест успешного получения районов для города из сессии
     */
    public function testGetDistrictsByCitySuccess(): void
    {
        City::query()->delete();
        District::query()->delete();

        // Явно создаём город в БД
        $city = City::create([
            'name' => 'Москва'
        ]);

        // Явно создаём районы для этого города
        District::create([
            'name' => 'Центральный',
            'city_id' => $city->id
        ]);
        District::create([
            'name' => 'Северный',
            'city_id' => $city->id
        ]);

        // Устанавливаем город в сессии
        Session::put('user_city', 'Москва');

        $response = $this->getJson('/api/districts-by-city');

        // Проверки
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(2, 'districts');
        $response->assertJsonFragment(['name' => 'Центральный']);
        $response->assertJsonFragment(['name' => 'Северный']);
    }

    /**
     * Тест получения районов для города по умолчанию (когда в сессии нет города)
     */
    public function testGetDistrictsByDefaultCity(): void
    {
        City::query()->delete();
        District::query()->delete();

        // Создаём Санкт‑Петербург (город по умолчанию) и район для него
        $city = City::create([
            'name' => 'Санкт-Петербург'
        ]);

        District::create([
            'name' => 'Адмиралтейский',
            'city_id' => $city->id
        ]);

        // В сессии нет города — должен использоваться город по умолчанию
        Session::forget('user_city');

        $response = $this->getJson('/api/districts-by-city');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(1, 'districts');
    }

    /**
     * Тест ошибки: город не найден в БД
     */
    public function testCityNotFound(): void
    {
        // В сессии — город, которого нет в БД
        Session::put('user_city', 'ВымышленныйГород');

        $response = $this->getJson('/api/districts-by-city');
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Город не найден'
        ]);
    }

    /**
     * Тест: город в сессии есть, но у него нет районов
     */
    public function testNoDistrictsForCity(): void
    {
        // Создаём город без районов
        City::create([
            'name' => 'Екатеринбург'
        ]);

        Session::put('user_city', 'Екатеринбург');

        $response = $this->getJson('/api/districts-by-city');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(0, 'districts');
    }
}
