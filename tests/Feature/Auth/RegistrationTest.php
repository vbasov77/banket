<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;


class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    /**
     * @return void
     */
    public function testRegistrationScreenCanBeRendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }


    /**
     * @return void
     */
    public function testNewUsersCanRegister(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Проверяем, что редирект начинается с маршрута my.obj
        $location = $response->headers->get('Location');
        $this->assertTrue(str_starts_with($location, route('my.obj')),
            "Ожидался редирект на маршрут my.obj, но получен: {$location}"
        );

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }
}
