<?php

namespace Tests\Feature\Favorite;

use App\Models\FavoriteSubj;
use App\Models\Subj;
use App\Models\User;
use App\Models\Obj;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexFavoriteTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    /** @test */
    /** @test */
    public function userCanViewTheirFavoritesList(): void
    {
        // Создаём пользователя
        $user = User::factory()->create();

        // Создаём несколько ресторанов (Subj)
        $restaurants = Subj::factory()->count(3)->create();

        // Добавляем рестораны в избранное для пользователя
        foreach ($restaurants as $restaurant) {
            FavoriteSubj::create([
                'user_id' => $user->id,
                'subj_id' => $restaurant->id,
            ]);
        }

        // Выполняем запрос от имени пользователя
        $response = $this->actingAs($user)
            ->get(route('favorites.subjs'));

        // Проверяем статус и контент
        $response->assertStatus(200);
        $response->assertSee($restaurants[0]->title); // Проверяем, что название ресторана есть в HTML
        $response->assertSee($restaurants[1]->title);
        $response->assertSee($restaurants[2]->title);

        // Дополнительно: проверяем, что страница загрузилась корректно
        $response->assertDontSee('Не авторизован');
    }

    /** @test */
    public function cannotViewFavoritesWithoutAuthentication(): void
    {
        $response = $this->get(route('favorites.subjs'));

        // Для веб‑маршрутов auth middleware делает редирект на страницу входа (302)
        $response->assertRedirect();
        // Можно дополнительно проверить URL редиректа
        $response->assertRedirect('/login'); // или другой URL вашей страницы входа
    }
}
