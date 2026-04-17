<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function testConfirmPasswordScreenCanBeRendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        // Отладка: выводим содержимое ответа при 500
        if ($response->status() === 500) {
            dump('Response content:', $response->getContent());
            dump('Session data:', session()->all());
            dump('Headers:', $response->headers->all());
        }

        $response->assertStatus(200);
    }

    public function testPasswordCanBeConfirmed(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
            '_token' => Str::random(40), // Явно передаём CSRF‑токен
        ]);

        // Отладка: выводим содержимое ответа при 500
        if ($response->status() === 500) {
            dump('Response content:', $response->getContent());
            dump('Session data:', session()->all());
            dump('Headers:', $response->headers->all());
        }

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function testPasswordIsNotConfirmedWithInvalidPassword(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}
