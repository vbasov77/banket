<?php

namespace Tests\Feature\Favorite;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\FavoriteSubj;
use App\Models\Subj;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

class StoreFavoriteTest extends TestCase
{
    use RefreshDatabase;
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        // Отключаем CSRF для всех тестов
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function userCanAddRestaurantToFavorites(): void
    {
        $user = User::factory()->create();
        $restaurant = Subj::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('favorites_subj.store', ['id' => $restaurant->id]));

        $response->assertJson(['message' => 'Добавлено в избранное']);
        $this->assertDatabaseHas('favorites_subj', [
            'user_id' => $user->id,
            'subj_id' => $restaurant->id,
        ]);
    }

    /** @test */
    public function cannotAddFavoriteWithoutAuthentication(): void
    {
        $restaurant = Subj::factory()->create();

        $response = $this->post(route('favorites_subj.store', ['id' => $restaurant->id]));

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Не авторизован']);
    }

    /** @test */
    public function cannotAddNonexistentRestaurantToFavorites(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('favorites_subj.store', ['id' => 999]));

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Ресторан не найден']);
    }

    /** @test */
    public function cannotAddDuplicateFavorite(): void
    {
        $user = User::factory()->create();
        $restaurant = Subj::factory()->create();

        FavoriteSubj::create([
            'user_id' => $user->id,
            'subj_id' => $restaurant->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('favorites_subj.store', ['id' => $restaurant->id]));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Уже в избранном']);
    }
}
