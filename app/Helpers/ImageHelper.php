<?php

namespace App\Helpers;

class ImageHelper
{
    public static function getResizedVkImageUrl(string $originalUrl, int $minWidth, int $maxWidth, int $quality = 96): string
    {
        // Шаг 1. Ищем все размеры в URL (NxM)
        preg_match_all('/(\d+)x(\d+)/', $originalUrl, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            // Нет размеров — просто обновляем quality
            return preg_replace('/[?&]quality=\d+/', '', $originalUrl) . "&quality=$quality";
        }

        // Шаг 2. Собираем ширины
        $widths = array_map(function ($match) {
            return (int)$match[1];
        }, $matches);

        // Шаг 3. Фильтруем по диапазону
        $validWidths = array_filter($widths, function ($w) use ($minWidth, $maxWidth) {
            return $w >= $minWidth && $w <= $maxWidth;
        });

        if (empty($validWidths)) {
            // Если нет в диапазоне — берём ближайший
            $distances = array_map(function ($w) use ($minWidth, $maxWidth) {
                if ($w < $minWidth) return $minWidth - $w;
                if ($w > $maxWidth) return $w - $maxWidth;
                return 0;
            }, $widths);
            $minDistance = min($distances);
            $closestIndex = array_search($minDistance, $distances);
            $targetWidth = $widths[$closestIndex];
        } else {
            $targetWidth = min($validWidths); // Минимальная подходящая ширина
        }

        // Шаг 4. Находим высоту для выбранной ширины
        $targetHeight = null;
        foreach ($matches as $match) {
            if ((int)$match[1] === $targetWidth) {
                $targetHeight = (int)$match[2];
                break;
            }
        }

        $newSize = "{$targetWidth}x{$targetHeight}";


        // Шаг 5. Заменяем первый найденный размер на новый
        $modifiedUrl = preg_replace('/\d+x\d+/', $newSize, $originalUrl, 1);

        // Шаг 6. Обновляем/добавляем quality
        $modifiedUrl = preg_replace('/[?&]quality=\d+/', '', $modifiedUrl); // Удаляем старые quality
        $modifiedUrl .= "&quality=$quality";

        return $modifiedUrl;
    }
}
