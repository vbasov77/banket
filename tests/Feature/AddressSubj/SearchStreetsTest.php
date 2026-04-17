<?php

namespace Tests\Feature\AddressSubj;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SearchStreetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Http::fake(); // Гарантированно сбрасываем все HTTP‑моки после каждого теста
        parent::tearDown();
    }

    /**
     * Проверяет, что ответ является валидным JSON и содержит ключ error
     */
    private function assertErrorResponse($response, string $errorMessage): void
    {
        $response->assertStatus(400)
            ->assertJson(['error' => $errorMessage]);

        $content = $response->getContent();
        $json = json_decode($content, true);
        $this->assertNotNull($json, 'Ответ не является валидным JSON');
        $this->assertArrayHasKey('error', $json, 'В ответе отсутствует ключ "error"');
    }

    /** @test */
    public function itValidatesRequiredParameters(): void
    {
        $errorMessage = 'Город и запрос обязательны, минимум 2 символа';

        // Случай 1: отсутствует город → ожидаем 400
        $response = $this->get('/api/streets?q=Ленина');
        $this->assertErrorResponse($response, $errorMessage);

        // Случай 2: отсутствует запрос → ожидаем 400
        $response = $this->get('/api/streets?city=Москва');
        $this->assertErrorResponse($response, $errorMessage);

        // Случай 3: запрос слишком короткий → ожидаем 400
        $response = $this->get('/api/streets?city=Москва&q=а');
        $this->assertErrorResponse($response, $errorMessage);
    }

    /** @test */
    /** @test */
    public function itSuccessfullySearchesStreetsWithoutMock(): void
    {
        // НЕ вызываем Http::fake() — используем реальное HTTP‑соединение

        $response = $this->get('/api/streets?city=Москва&q=Ленина');

        $status = $response->status();
        $content = $response->getContent();

        Log::info('Test Result WITHOUT mock', [
            'status' => $status,
            'content' => $content
        ]);

        // Проверяем, что ответ успешный (200) и содержит данные
        $response->assertStatus(200);
    }

    /** @test */
    public function itHandlesEmptyResultsFromApi(): void
    {
        // Очищаем моки перед тестом
        Http::fake();

        // Устанавливаем мок, возвращающий пустой массив
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([], 200)
        ]);

        $response = $this->get('/api/streets?city=Москва&q=Вымышленная');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

}
