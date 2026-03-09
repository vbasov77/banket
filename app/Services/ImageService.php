<?php


namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class ImageService extends Service
{
    /**
     * @param UploadedFile $file
     * @param string $disk
     * @param string $path
     * @param int $quality
     * @return array
     */
    public function compressImageIfLarge(
        UploadedFile $file,
        string $path = 'resized/',
        int $quality = 95
    ): array
    {
        try {
            // Создаём менеджер с драйвером
            $manager = new ImageManager(new Driver());
            $const = 1200;
            $image = $manager->read($file->getPathname());
            $height = $image->height();
            $width = $image->width();

            if ($image->width() > $const) {
                $newWidth = $const;
                $newHeight = ($newWidth * $height) / $width;
                $image->resize($newWidth, $newHeight);
            }

            // Определяем формат и кодируем с нужным качеством
            $format = strtolower($file->extension());
            if (in_array($format, ['jpg', 'jpeg'])) {
                $encodedImage = $image->toJpeg($quality);
            } elseif ($format === 'png') {
                $encodedImage = $image->toPng(6); // уровень сжатия 6 (0–9)
            } elseif ($format === 'webp') {
                $encodedImage = $image->toWebp($quality);
            } else {
                // Для остальных форматов — JPEG с заданным качеством
                $encodedImage = $image->toJpeg($quality);
            }

            // Генерируем уникальное имя
            $filename = md5(uniqid() . $file->getClientOriginalName()) . '.' . $format;
            $fullPath = $path . $filename;

            // Формируем полный путь в публичной директории
            $publicFullPath = public_path($fullPath);

            // Создаём директорию, если её нет
            $directory = dirname($publicFullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Сохраняем файл напрямую в публичную директорию
            file_put_contents($publicFullPath, (string)$encodedImage);

            return [
                'success' => true,
                'path' => $fullPath,
                'size' => filesize($publicFullPath),
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'path' => null,
                'size' => null,
                'error' => $e->getMessage()
            ];
        }
    }

}

