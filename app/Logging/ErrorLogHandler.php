<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ErrorLogHandler extends StreamHandler
{
    public function __construct(string $filePath)
    {
        // Устанавливаем уровень логирования — только ошибки и фатальные ошибки
        parent::__construct($filePath, Logger::ERROR);
    }

    protected function write(array|\Monolog\LogRecord $record): void
    {
        $record = $this->ensureUtf8($record);
        parent::write($record);
    }

    private function ensureUtf8(array $record): array
    {
        foreach ($record as $key => $value) {
            if (is_string($value)) {
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'auto') ?: '�';
                }
                $record[$key] = $value;
            } elseif (is_array($value)) {
                $record[$key] = $this->ensureUtf8($value);
            }
        }
        return $record;
    }
}
