<?php

namespace Tests\Feature;

use App\Models\ImgSubj;
use App\Models\Subj;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ImgSubjControllerTest extends TestCase
{

    use WithoutMiddleware;
    use RefreshDatabase {
        refreshDatabase as protected;
    }


    protected function refreshDatabase()
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations',
            '--env' => 'testing'
        ]);
    }

    /**
     * Тест успешного удаления записи
     */
    public function testDestroySuccess(): void
    {
        $imgSubj = ImgSubj::factory()->create();

        $response = $this
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])
            ->delete('/delete_subj_img/' . $imgSubj->id);

        $response
            ->assertStatus(200)
            ->assertJson([
                'answer' => 'ok'
            ]);
    }

    /**
     * Тест удаления несуществующей записи
     */
    public function testDestroyNonExistingId(): void
    {
        // Пытаемся удалить запись с несуществующим ID
        $response = $this->delete('/delete_subj_img/9999');

        // Проверяем ответ — ожидаем 404 для несуществующего ID
        $response->assertStatus(404);

        // Дополнительно проверяем структуру JSON‑ответа
        $response->assertJson([
            'error' => 'Image not found'
        ]);
    }

    /**
     * Тест с некорректным типом ID (строка вместо числа)
     */
    public function testDestroyInvalidIdType(): void
    {
        // Отправляем ID в виде строки
        $response = $this->delete('/delete_subj_img/invalid_string');

        // Проверяем ответ — ожидаем 400 для некорректного формата ID
        $response->assertStatus(400);

        // Проверяем JSON‑ответ
        $response->assertJson([
            'error' => 'Invalid ID format'
        ]);
    }
}
