<?php

namespace Tests\Feature\DetailsObj;

use App\Http\Controllers\DetailsObjController;
use App\Http\Requests\DetailsObj\CreateDetailsObjRequest;
use App\Services\DetailsObjService;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Mockery;
use Tests\TestCase;

class DetailsObjStoreTest extends TestCase
{
    use WithoutMiddleware;

    protected DetailsObjController $controller;
    protected DetailsObjService $mockDetailsObjService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockDetailsObjService = Mockery::mock(DetailsObjService::class);

        $this->controller = new DetailsObjController(
            Mockery::mock(\App\Services\ObjService::class),
            Mockery::mock(\App\Services\ImgObjService::class),
            $this->mockDetailsObjService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    /**
     * Тест: успешный вызов store() с валидными данными
     */
    public function testStoreSuccess(): void
    {
        // ARRANGE: подготавливаем валидные тестовые данные
        $validData = [
            'obj_id' => 1,
            'service' => ['Ведущий/Тамада', 'Фотограф/Видеооператор'],
            'for_events' => ['Свадьба', 'День рождения'],
            'kitchen' => ['Европейская', 'Русская'],
            'alcohol' => 2,
            'alcohol_price' => 500,
            'more' => 1,
            'more_price' => 300,
            'payment_methods' => ['Наличные', 'Карта'],
            'text_obj' => 'Подробное описание объекта длиной более 10 символов',
        ];

        // Создаём мок запроса
        $mockRequest = Mockery::mock(CreateDetailsObjRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn($validData);

        // Ожидаем вызов метода store сервиса
        $this->mockDetailsObjService
            ->shouldReceive('store')
            ->with($validData)
            ->once()
            ->andReturn((object)['id' => 1]);

        // ACT: вызываем тестируемый метод
        $response = $this->controller->store($mockRequest);

        // ASSERT: проверяем результаты
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->status());
        $this->assertEquals(route('create.subj'), $response->getTargetUrl());
        $this->assertFalse(session()->has('errors'));
    }

    /**
     * Вспомогательный метод для создания валидатора с ошибками
     * @param array $errors
     * @return \Illuminate\Contracts\Validation\Validator
     */
    /**
     * Вспомогательный метод для создания валидатора с ошибками
     * @param array $errors
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function buildValidatorWithErrors(array $errors): \Illuminate\Contracts\Validation\Validator
    {
        $validatorMock = Mockery::mock(\Illuminate\Contracts\Validation\Validator::class);
        $validatorMock->shouldReceive('fails')->andReturn(true);

        // Создаём реальный MessageBag с правильной структурой
        $messageBag = new \Illuminate\Support\MessageBag();

        foreach ($errors as $field => $message) {
            $messageBag->add($field, $message);
        }

        $validatorMock->shouldReceive('errors')->andReturn($messageBag);
        return $validatorMock;
    }

    /**
     * Тест: ошибка валидации — отсутствует обязательный obj_id
     */
    public function testStoreValidationMissingObjId(): void
    {
        $mockRequest = Mockery::mock(CreateDetailsObjRequest::class);
        $mockRequest->shouldReceive('validated')
            ->andThrow(new \Illuminate\Validation\ValidationException(
                $this->buildValidatorWithErrors(['obj_id' => 'Поле ID объекта обязательно для заполнения'])
            ));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->store($mockRequest);
    }


    /**
     * Тест: ошибка валидации — alcohol=2 без alcohol_price
     */
    public function testStoreValidationAlcoholWithoutPrice(): void
    {
        $invalidData = [
            'obj_id' => 1,
            'service' => ['Ведущий/Тамада'],
            'for_events' => ['Свадьба'],
            'kitchen' => ['Европейская'],
            'alcohol' => 2,
            'alcohol_price' => null, // Ошибка: цена не указана
            'more' => 1,
            'more_price' => 300,
            'payment_methods' => ['Наличные'],
            'text_obj' => 'Описание длиной более 10 символов',
        ];

        $mockRequest = Mockery::mock(CreateDetailsObjRequest::class);
        $mockRequest->shouldReceive('validated')
            ->andThrow(new ValidationException($this->buildValidatorWithErrors(['alcohol_price' => 'Цена должна быть указана и быть больше нуля, если выбран вариант "За отдельную плату".'])));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->store($mockRequest);
    }

    /**
     * Тест: ошибка сохранения в сервисе
     */
    public function testStoreSaveError(): void
    {
        $validData = [
            'obj_id' => 1,
            'service' => ['Ведущий/Тамада'],
            'for_events' => ['Свадьба'],
            'kitchen' => ['Европейская'],
            'alcohol' => 2,
            'alcohol_price' => 500,
            'more' => 1,
            'more_price' => 300,
            'payment_methods' => ['Наличные'],
            'text_obj' => 'Описание длиной более 10 символов',
        ];

        $mockRequest = Mockery::mock(CreateDetailsObjRequest::class);
        $mockRequest->shouldReceive('validated')->andReturn($validData);

        $this->mockDetailsObjService
            ->shouldReceive('store')
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');
        $this->controller->store($mockRequest);
    }

}
