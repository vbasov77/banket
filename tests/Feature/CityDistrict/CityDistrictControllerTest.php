<?php

namespace Tests\Feature\CityDistrict;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\CreatesApplication;
use Tests\TestCase;

class CityDistrictControllerTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Тест отображения формы создания города и районов
     */
    public function testCreateFormDisplaysSuccessfully(): void
    {
        $response = $this->get(route('city-district.create'));

        $response->assertStatus(200);
        $response->assertViewIs('city-district.create');
    }

    /**
     * Тест успешного создания нового города и районов
     */
    public function testStoreNewCityAndDistrictsSuccessfully(): void
    {
        $response = $this->setDataCityDistrict();

        // Проверяем редирект
        $response->assertRedirect(route('city-district.create'));
        // Проверяем, что город создан
        $this->assertDatabaseHas('cities', [
            'name' => 'Москва'
        ]);

        $city = City::where('name', 'Москва')->first();

        // Проверяем, что районы созданы
        $this->assertDatabaseHas('districts', [
            'city_id' => $city->id,
            'name' => 'Центральный'
        ]);
        $this->assertDatabaseHas('districts', [
            'city_id' => $city->id,
            'name' => 'Северный'
        ]);
    }

    /**
     * Тест валидации: обязательные поля отсутствуют
     */
    public function testValidationFailsWhenRequiredFieldsMissing(): void
    {
        // Создаём пользователя и начинаем сессию
        $user = User::factory()->create();
        $this->startSession();
        $token = csrf_token();

        // Отправляем запрос БЕЗ обязательных полей
        $data = [
            '_token' => $token,
            // city_name отсутствует
            // districts отсутствует
        ];

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post(route('city-district.store'), $data);

        // Проверяем, что валидация провалилась и ошибки записаны в сессию
        $response->assertSessionHasErrors(['city_name', 'districts']);

        // Проверяем редирект (обычно на форму с ошибками)
        $response->assertRedirect();
    }

    /**
     * Тест валидации: слишком длинное название города
     */
    public function testValidationFailsWhenCityNameTooLong(): void
    {
        $longName = str_repeat('A', 256); // 256 символов
        City::query()->delete();
        District::query()->delete();

        $user = User::factory()->create();

        // Инициализируем сессию и получаем CSRF‑токен
        $this->startSession();
        $token = csrf_token();

        $data = [
            'city_name' => $longName,
            'districts' => "Центральный;\nСеверный;",
            '_token' => $token, // явно передаём токен
        ];

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token]) // добавляем токен в сессию
            ->post(route('city-district.store'), $data);

        $response->assertSessionHasErrors('city_name');
    }

    /**
     * Тест обработки дублирующихся районов
     */
    public function testHandlesDuplicateDistricts(): void
    {
        City::query()->delete();
        District::query()->delete();

        $this->startSession();
        $token = csrf_token();

        $existingCity = City::create(['name' => 'Екатеринбург']);

        // Добавляем один район
        District::create([
            'name' => 'Ленинский',
            'city_id' => $existingCity->id,
        ]);

        $data = [
            'city_name' => 'Екатеринбург',
            'districts' => "Ленинский;\nОктябрьский;",
            '_token' => $token
        ];

        $response = $this->post(route('city-district.store'), $data);

        $response->assertRedirect(route('city-district.create'));

        // Проверяем, что дубликат не создан (остаётся 1 Ленинский)
        $leninskyCount = District::where('city_id', $existingCity->id)
            ->where('name', 'Ленинский')
            ->count();
        $this->assertEquals(1, $leninskyCount);

        // Проверяем, что новый район добавлен
        $this->assertDatabaseHas('districts', [
            'city_id' => $existingCity->id,
            'name' => 'Октябрьский'
        ]);
    }

    /**
     * Тест обработки пустого ввода районов
     */
    public function testHandlesEmptyDistrictsInput(): void
    {

        City::query()->delete();
        District::query()->delete();

        // Инициализируем сессию и получаем CSRF‑токен
        $this->startSession();
        $token = csrf_token();
        $data = [
            'city_name' => 'Новосибирск',
            'districts' => '',
            '_token' => $token
        ];

        $response = $this->post(route('city-district.store'), $data);

        $response->assertSessionHasErrors('districts');
    }


    public function setDataCityDistrict()
    {
        City::query()->delete();
        District::query()->delete();

        $user = User::factory()->create();

        // Инициализируем сессию и получаем CSRF‑токен
        $this->startSession();
        $token = csrf_token();

        $data = [
            'city_name' => 'Москва',
            'districts' => "Центральный;\nСеверный;",
            '_token' => $token, // явно передаём токен
        ];

        return $this->actingAs($user)
            ->withSession(['_token' => $token]) // добавляем токен в сессию
            ->post(route('city-district.store'), $data);

    }
}
