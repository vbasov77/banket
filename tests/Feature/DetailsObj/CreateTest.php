<?php

namespace Tests\Feature\DetailsObj;

use App\Http\Controllers\DetailsObjController;
use App\Services\ObjService;
use App\Services\ImgObjService;
use App\Services\DetailsObjService;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use WithoutMiddleware;

    protected DetailsObjController $controller;
    protected ObjService $mockObjService;
    protected ImgObjService $mockImgService;
    protected DetailsObjService $mockDetailsObjService;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём моки для всех трёх сервисов
        $this->mockObjService = Mockery::mock(ObjService::class);
        $this->mockImgService = Mockery::mock(ImgObjService::class);
        $this->mockDetailsObjService = Mockery::mock(DetailsObjService::class);

        // Передаём все три мока в конструктор контроллера
        $this->controller = new DetailsObjController(
            $this->mockObjService,
            $this->mockImgService,
            $this->mockDetailsObjService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Тест: метод create возвращает правильный view с данными
     */
    public function testCreateReturnsViewWithObjData(): void
    {
        // ARRANGE: подготавливаем тестовые данные
        $expectedObj = (object)['id' => 1, 'name' => 'Test Object', 'user_id' => 1];

        // Ожидаем, что метод findObjByUserId будет вызван и вернёт тестовый объект
        $this->mockObjService
            ->shouldReceive('findObjByUserId')
            ->once()
            ->andReturn($expectedObj);

        // ACT: вызываем тестируемый метод
        $response = $this->controller->create();

        // ASSERT: проверяем результаты
        // Проверяем, что возвращается экземпляр View
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);

        // Проверяем имя view
        $this->assertEquals('details_obj.create', $response->getName());

        // Проверяем данные, переданные в view
        $data = $response->getData();
        $this->assertArrayHasKey('obj', $data);
        $this->assertEquals($expectedObj, $data['obj']);
    }

    /**
     * Тест: обработка случая, когда объект не найден
     */
    public function testCreateHandlesNullObj(): void
    {
        // ARRANGE
        $this->mockObjService
            ->shouldReceive('findObjByUserId')
            ->once()
            ->andReturn(null);

        // ACT
        $response = $this->controller->create();

        // ASSERT
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertEquals('details_obj.create', $response->getName());

        $data = $response->getData();
        $this->assertArrayHasKey('obj', $data);
        $this->assertNull($data['obj']);
    }
}
