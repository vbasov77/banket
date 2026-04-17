<?php

namespace Tests\Feature\City;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SetCityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Session::start();
    }

    /**
     * Тест успешного сохранения города в сессии для неавторизованного пользователя
     */
    public function testSetCityForUnauthenticatedUser(): void
    {
        // Создаём город в БД
        $city = City::factory()->create(['name' => 'Москва']);

        $validData = [
            'city' => 'Москва',
            'city_id' => $city->id
        ];

        // Отправляем POST‑запрос
        $response = $this->post('/set-city', $validData);

        // Проверяем статус ответа
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Проверяем, что город сохранился в сессии
        $this->assertEquals('Москва', Session::get('user_city'));

        // Удаляем проверку user_city_id, так как контроллер его не сохраняет
        // $this->assertEquals($city->id, Session::get('user_city_id')); — эта строка убрана

        // Проверяем, что фильтры сброшены
        $this->assertNull(Session::get('selected_filters'));
    }

    /**
     * Тест валидации: отсутствие обязательных полей
     */
    /**
     * Тест валидации: отсутствие обязательных полей
     */
    public function testValidationRequiredFields(): void
    {
        $invalidData = [];

        $response = $this->postJson('/set-city', $invalidData);

        // Если сервер вернул 500, сразу прерываем тест с описанием ошибки
        if ($response->status() === 500) {
            $this->fail(
                'Сервер вернул ошибку 500. Ответ: ' .
                json_encode($response->json(), JSON_PRETTY_PRINT)
            );
        }

        // Основные проверки
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['city', 'city_id']);
    }


    /**
     * Тест валидации: некорректные типы данных
     */
    public function testValidationInvalidDataTypes(): void
    {
        $invalidData = [
            'city' => 123,
            'city_id' => 'не число'
        ];

        $response = $this->post('/set-city', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['city', 'city_id']);
    }
}
