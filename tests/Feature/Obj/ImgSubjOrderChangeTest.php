<?php

namespace Tests\Feature\Obj;

use App\Models\User;
use App\Models\Obj;
use App\Models\Subj;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImgSubjOrderChangeTest extends TestCase
{
    use RefreshDatabase;

    public function testImgOrderChangeSuccess(): void
    {
        // 1. Создаём владельца
        $user = User::factory()->create();

        // 2. Создаём Obj с этим пользователем (владелец объекта)
        $obj = Obj::factory()->create(['user_id' => $user->id]);

        // 3. Создаём Subj, привязывая его к Obj
        $subj = Subj::factory()->forObj($obj)->create();

        // Данные для POST-запроса (подставь реальные поля твоего контроллера)
        $payload = [
            'order' => [1, 2, 3],
            // 'token' => ...,
        ];

        $response = $this->actingAs($user)
            ->post(route('img_subj.order_change', ['subjId' => $subj->id]), $payload);

        // Подставь ожидаемый статус: 200, 201, 302 — зависит от контроллера
        $response->assertStatus(200)
            ->assertJson([
                // если контроллер возвращает JSON — укажи ожидаемые ключи
                // 'success' => true,
            ]);
    }

    public function testImgOrderChangeForbiddenForNonOwner(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $obj = Obj::factory()->create(['user_id' => $owner->id]);
        $subj = Subj::factory()->forObj($obj)->create();

        $payload = ['order' => [1]];

        $response = $this->actingAs($anotherUser)
            ->post(route('img_subj.order_change', ['subjId' => $subj->id]), $payload);

        $response->assertStatus(403);
    }
}
