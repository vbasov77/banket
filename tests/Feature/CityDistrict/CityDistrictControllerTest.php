<?php

namespace Tests\Feature\CityDistrict;

use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        City::query()->delete();
        District::query()->delete();

        $data = [
            'city_name' => 'Москва',
            'districts' => "Центральный;\nСеверный;\nЗападный;"
        ];

        $response = $this->post(route('city-district.store'), $data);

        // Проверяем редирект
        $response->assertRedirect(route('city-district.create'));
        $response->assertSessionHas('success', 'Город и районы успешно добавлены!');

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
        $this->assertDatabaseHas('districts', [
            'city_id' => $city->id,
            'name' => 'Западный'
        ]);
    }

    /**
     * Тест добавления районов к существующему городу
     */
    public function testStoreDistrictsToExistingCity(): void
    {
        City::query()->delete();
        District::query()->delete();


        // Создаём существующий город
        $existingCity = City::create(['name' => 'Санкт-Петербург']);

        $data = [
            'city_name' => 'Санкт-Петербург',
            'districts' => "Адмиралтейский;\nВасилеостровский;"
        ];

        $response = $this->post(route('city-district.store'), $data);

        $response->assertRedirect(route('city-district.create'));
        $response->assertSessionHas('success', 'Районы успешно добавлены к существующему городу!');

        // Проверяем, что новый город не создан
        $this->assertCount(1, City::all());

        // Проверяем, что районы добавлены к существующему городу
        $this->assertDatabaseHas('districts', [
            'city_id' => $existingCity->id,
            'name' => 'Адмиралтейский'
        ]);
        $this->assertDatabaseHas('districts', [
            'city_id' => $existingCity->id,
            'name' => 'Василеостровский'
        ]);
    }

    /**
     * Тест валидации: обязательные поля отсутствуют
     */
    public function testValidationFailsWhenRequiredFieldsMissing(): void
    {
        $data = [
            'city_name' => '',
            'districts' => ''
        ];

        $response = $this->post(route('city-district.store'), $data);

        $response->assertSessionHasErrors(['city_name', 'districts']);
        $response->assertRedirect();
    }

    /**
     * Тест валидации: слишком длинное название города
     */
    public function testValidationFailsWhenCityNameTooLong(): void
    {
        $longName = str_repeat('A', 256); // 256 символов

        $data = [
            'city_name' => $longName,
            'districts' => 'Район;'
        ];

        $response = $this->post(route('city-district.store'), $data);

        $response->assertSessionHasErrors('city_name');
    }

    /**
     * Тест обработки дублирующихся районов
     */
    public function testHandlesDuplicateDistricts(): void
    {
        City::query()->delete();
        District::query()->delete();

        $existingCity = City::create(['name' => 'Екатеринбург']);
        // Добавляем один район
        District::create([
            'name' => 'Ленинский',
            'city_id' => $existingCity->id
        ]);

        $data = [
            'city_name' => 'Екатеринбург',
            'districts' => "Ленинский;\nОктябрьский;"
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
        $data = [
            'city_name' => 'Новосибирск',
            'districts' => ''
        ];

        $response = $this->post(route('city-district.store'), $data);

        $response->assertSessionHasErrors('districts');
    }
}
