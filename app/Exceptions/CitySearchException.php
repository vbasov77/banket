<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use RuntimeException;

/**
 * Исключение для ошибок, связанных с поиском городов
 *
 * Позволяет передавать кастомное сообщение и HTTP‑статус
 */
class CitySearchException extends RuntimeException
{
    /**
     * HTTP-статус код для ответа
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * Конструктор исключения
     *
     * @param string $message Сообщение об ошибке
     * @param int $statusCode HTTP-статус код (по умолчанию 400)
     * @param int $code Код ошибки (по умолчанию 0)
     * @param \Throwable|null $previous Предыдущее исключение
     */
    public function __construct(
        string $message,
        int $statusCode = Response::HTTP_BAD_REQUEST, // 400
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * Получить HTTP-статус код
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Установить HTTP-статус код
     *
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }
}
