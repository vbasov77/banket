<?php

namespace Tests\Feature\Favorite;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\FavoriteSubj;
use App\Models\Subj;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

class DestroyFavoriteTest extends TestCase
{
    use RefreshDatabase;
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function userCanRemoveRestaurantFromFavorites(): void
    {
        $user = User::factory()->create();
        $restaurant = Subj::factory()->create();
        $favorite = FavoriteSubj::create([
            'user_id' => $user->id,
            'subj_id' => $restaurant->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('favorites_subj.destroy', ['id' => $restaurant->id]));

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Удалено из избранного']);
        $this->assertDatabaseMissing('favorites_subj', ['id' => $favorite->id]);
    }

    /** @test */
    public function cannotRemoveFavoriteWithoutAuthentication(): void
    {
        $restaurant = Subj::factory()->create();

        $response = $this->delete(route('favorites_subj.destroy', ['id' => $restaurant->id]));

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Не авторизован']);
    }

    /** @test */
    public function cannotRemoveNonexistentFavorite(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('favorites_subj.destroy', ['id' => 999]));

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Не найдено в избранном']);
    }
}
