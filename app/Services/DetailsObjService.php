<?php

namespace App\Services;

use App\Repositories\DetailsObjRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DetailsObjService extends Service
{
    private DetailsObjRepository $detailsRepository;

    public function __construct(DetailsObjRepository $detailsRepository)
    {
        $this->detailsRepository = $detailsRepository;
    }

    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function findById(int $id): mixed
    {
        try {
            $obj = $this->detailsRepository->findById($id);
            if (!$obj) {
                throw new ModelNotFoundException(
                    "Объект с ID {$id} не найден"
                );
            }
            return $obj;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в DetailsObjService@findById: ' . $e->getMessage(),
                [
                    'requested_id' => $id,
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function update(array $data): void
    {
        try {
            $newData = $this->getValidate($data);
            $this->detailsRepository->update($newData);
        } catch (InvalidArgumentException $e) {
            Log::channel('error_file')->error(
                'Ошибка валидации данных в DetailsObjService@update: ' . $e->getMessage(),
                [
                    'input_data' => $data,
                    'user_id' => auth()->id()
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в DetailsObjService@update: ' . $e->getMessage(),
                [
                    'input_data' => $data,
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function getValidate(array $data): array
    {

        try {
            // Проверка обязательных полей
            if (!isset($data['obj_id'])) {
                throw new InvalidArgumentException('Отсутствует ID объекта');
            }

            $alcoholValue = $data['alcohol'] ?? null;
            $alcoholPrice = $data['alcohol_price'] ?? 0;
            $moreValue = $data['more'] ?? null;
            $morePrice = $data['more_price'] ?? 0;

            // Валидация типов данных
            if (!is_numeric($alcoholValue) || !is_numeric($alcoholPrice) ||
                !is_numeric($moreValue) || !is_numeric($morePrice)) {
                throw new InvalidArgumentException('Некорректный тип данных для полей alcohol/more или цен');
            }

            // Форматируем значение для сохранения в JSON
            $alcoholJson = (string)$alcoholValue;
            if ((int)$alcoholValue === 2 && (float)$alcoholPrice > 0) {
                $alcoholJson .= ':' . (float)$alcoholPrice;
            }
            $data['alcohol'] = $alcoholJson;

            $moreJson = (string)$moreValue;
            if ((int)$moreValue === 2 && (float)$morePrice > 0) {
                $moreJson .= ':' . (float)$morePrice;
            }
            $data['more'] = $moreJson;

            unset($data['more_price'], $data['alcohol_price']);

            return $data;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в getValidate: ' . $e->getMessage(),
                [
                    'input_data' => $data,
                    'user_id' => auth()->id()
                ]
            );
            throw new InvalidArgumentException('Ошибка валидации данных: ' . $e->getMessage());
        }
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data): mixed
    {
        try {
            $newData = $this->getValidate($data);
            return $this->detailsRepository->store($newData);
        } catch (ValidationException $e) {
            Log::channel('error_file')->error(
                'Ошибка валидации в DetailsObjService@store: ' . $e->getMessage(),
                [
                    'input_data' => $data,
                    'errors' => $e->errors(),
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в DetailsObjService@store: ' . $e->getMessage(),
                [
                    'processed_data' => $newData ?? $data,
                    'exception_class' => get_class($e),
                    'exception_code' => $e->getCode()
                ]
            );
            throw new \RuntimeException('Ошибка сохранения данных в сервисе', 0, $e);
        }
    }
}

