<?php


namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class ImageService extends Service
{
    /**
     * @param UploadedFile $file
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
            // Проверяем валидность загруженного файла
            if (!$file->isValid()) {
                throw new \Exception('Файл не прошёл валидацию загрузки');
            }

            // Создаём менеджер с драйвером
            $manager = new ImageManager(new Driver());
            $const = 1200;
            $image = $manager->read($file->getPathname());
            $height = $image->height();
            $width = $image->width();

            if ($image->width() > $const) {
                $newWidth = $const;
                $newHeight = (int)($newWidth * $height / $width); // Приводим к целому числу
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
                $format = 'jpg'; // Корректируем формат для имени файла
            }

            // Генерируем уникальное имя
            $filename = md5(uniqid() . $file->getClientOriginalName()) . '.' . $format;
            $fullPath = $path . $filename;

            // Формируем полный путь в публичной директории
            $publicFullPath = public_path($fullPath);

            // Создаём директорию, если её нет
            $directory = dirname($publicFullPath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new \Exception('Не удалось создать директорию для сохранения изображения: ' . $directory);
                }
            }

            // Сохраняем файл напрямую в публичную директорию
            $bytesWritten = file_put_contents($publicFullPath, (string)$encodedImage);
            if ($bytesWritten === false) {
                throw new \Exception('Ошибка записи файла на диск');
            }

            $fileSize = filesize($publicFullPath);
            if ($fileSize === false) {
                throw new \Exception('Не удалось получить размер сохранённого файла');
            }

            return [
                'success' => true,
                'path' => $fullPath,
                'size' => $fileSize,
                'error' => null
            ];

        } catch (\InvalidArgumentException $e) {
            Log::channel('error_file')->error(
                'Invalid image format in compressImageIfLarge: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getMimeType(),
                ]
            );

            return [
                'success' => false,
                'path' => null,
                'size' => null,
                'error' => 'Неподдерживаемый формат изображения'
            ];
        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error(
                'Image processing error in compressImageIfLarge: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'file_name' => $file->getClientOriginalName(),
                ]
            );

            return [
                'success' => false,
                'path' => null,
                'size' => null,
                'error' => 'Ошибка обработки изображения'
            ];
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Unexpected error in compressImageIfLarge: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'file_name' => $file->getClientOriginalName() ?? 'unknown',
                    'file_size' => $file->getSize() ?? 'unknown',
                ]
            );

            return [
                'success' => false,
                'path' => null,
                'size' => null,
                'error' => 'Произошла непредвиденная ошибка при обработке изображения'
            ];
        }
    }


}

