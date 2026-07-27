<?php

namespace Tests\Feature\Obj;

use App\Models\User;
use App\Models\Obj;
use App\Models\Subj;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ImgSubjStoreTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Мокаем HTTP-клиент (для успешного сценария)
        Http::fake([
            'https://api.imageban.ru/v1' => Http::response([
                'success' => true,
                'data' => [
                    'id' => 12345,
                    'link' => 'https://img.imageban.ru/out/12345.jpg',
                ],
            ], 200),
        ]);

        // ВАЖНО: корректно замокаем цепочку Log::channel()->error()
        Log::shouldReceive('channel->error')->andReturn(null)->atLeast()->once();
    }

    public function testStoresImageSuccessfully(): void
    {
        $user = User::factory()->create();

        $obj = Obj::factory()->create(['user_id' => $user->id]);
        $subj = Subj::factory()->forObj($obj)->create();

        // Любой файл, GD не нужен
        $file = UploadedFile::fake()->create('photo.jpg', 1024, 'image/jpeg');

        $response = $this->actingAs($user)
            ->post(route('img_subj.store'), [
                'id' => $subj->id,
                'img' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['path', 'id', 'message'])
            ->assertJson([
                'message' => 'Изображение успешно загружено',
            ]);

        // Проверяем, что был реальный запрос к API imageban.ru (два раза: big + small)
        Http::assertSentCount(2);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.imageban.ru/v1';
        });

        // Проверяем, что в базе появилась запись (хотя бы одна картинка)
        $this->assertDatabaseHas('img_ban_subj', [
            'subj_id' => $subj->id,
            'big_id' => 12345, // ID из мок-ответа
        ]);
    }

    public function testReturns403WhenUserIsNotAuthor(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $obj = Obj::factory()->create(['user_id' => $owner->id]);
        $subj = Subj::factory()->forObj($obj)->create();

        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');

        $response = $this->actingAs($anotherUser)
            ->post(route('img_subj.store'), [
                'id' => $subj->id,
                'img' => $file,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'answer' => 'error',
                'message' => 'У вас нет прав на удаление этого объекта',
            ]);

        // Важно: при отказе по правам сервис вообще не должен пытаться грузить на imageban
        Http::assertNothingSent();
    }
}
