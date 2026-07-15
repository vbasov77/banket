<?php

namespace Tests\Feature\Obj;

use App\Models\User;
use App\Models\Subj;
use App\Models\AddressSubj;
use App\Models\Obj;
use App\Services\ImgSubjService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class EditSubjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRedirectsIfAddressNotExists(): void
    {
        $user = User::factory()->create();
        $obj = Obj::factory()->create(['user_id' => $user->id]);
        $subj = Subj::factory()->create(['obj_id' => $obj->id]);

        $this->actingAs($user)
            ->get(route('edit.img_subj', ['id' => $subj->id]))
            ->assertRedirect(route('map.edit', [
                'id' => $subj->id,
                'error' => 'Сначала добавьте адрес',
            ]));
    }

    public function testShowsEditPageWhenAddressExistsAndUserIsAuthor(): void
    {
        $user = User::factory()->create();
        $obj = Obj::factory()->create(['user_id' => $user->id]);
        $subj = Subj::factory()->create(['obj_id' => $obj->id]);

        // Создаём адрес, чтобы пройти проверку $address->exists()
        AddressSubj::factory()->create(['subj_id' => $subj->id]);

        $mockService = Mockery::mock(ImgSubjService::class);
        $mockService->shouldReceive('findImgBySubjId')
            ->with($subj->id)
            ->andReturn([]);

        $this->app->instance(ImgSubjService::class, $mockService);

        $response = $this->actingAs($user)
            ->get(route('edit.img_subj', ['id' => $subj->id]));

        $response->assertStatus(200)
            ->assertViewIs('img_subj.edit')
            ->assertViewHas('subj', $subj->id)
            ->assertViewHas('images');
    }

    public function testDeniesAccessIfUserIsNotAuthor(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $obj = Obj::factory()->create(['user_id' => $owner->id]);
        $subj = Subj::factory()->create(['obj_id' => $obj->id]);
        AddressSubj::factory()->create(['subj_id' => $subj->id]);

        $this->actingAs($anotherUser)
            ->get(route('edit.img_subj', ['id' => $subj->id]))
            ->assertViewIs('errors.unauthorized');
    }

    public function testRedirectsOnInvalidId(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('edit.img_subj', ['id' => 'invalid']))
            ->assertStatus(302)
            ->assertRedirect(route('map.edit', [
                'id' => 'invalid', // <-- вот тут была ошибка: контроллер не делает (int), он передаёт строку
                'error' => 'Сначала добавьте адрес',
            ]));
    }
}
