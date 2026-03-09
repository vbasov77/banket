<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;

class Utf8LogHandler extends StreamHandler
{
    protected function write(array|\Monolog\LogRecord $record): void
    {
        // Гарантируем UTF‑8 перед записью
        $record = $this->ensureUtf8($record);
        parent::write($record);
    }

    private function ensureUtf8(array $record): array
    {
        foreach ($record as $key => $value) {
            if (is_string($value)) {
                // Принудительно конвертируем в UTF‑8
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-16LE') ?: $value;
                $record[$key] = mb_check_encoding($value, 'UTF-8') ? $value : '�';
            } elseif (is_array($value)) {
                $record[$key] = $this->ensureUtf8($value);
            }
        }
        return $record;
    }
}
