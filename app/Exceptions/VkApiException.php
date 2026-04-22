<?php

namespace App\Exceptions;

use Exception;

class VkApiException extends Exception
{
    protected int $vkErrorCode;
    protected array $vkErrorDetails;

    public function __construct(
        string $message,
        int $vkErrorCode = 0,
        array $vkErrorDetails = [],
        ?Exception $previous = null
    ) {
        $this->vkErrorCode = $vkErrorCode;
        $this->vkErrorDetails = $vkErrorDetails;
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): int
    {
        return $this->vkErrorCode;
    }

    public function getErrorDetails(): array
    {
        return $this->vkErrorDetails;
    }
}
