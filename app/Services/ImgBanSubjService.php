<?php


namespace App\Services;


use App\Models\ImgBanSubj;
use App\Repositories\ImgBanRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImgBanSubjService extends Service
{
    protected ImgBanRepository $imgBanRepository;

    /**
     * @param ImgBanRepository $imgBanRepository
     */
    public function __construct(ImgBanRepository $imgBanRepository)
    {
        $this->imgBanRepository = $imgBanRepository;
    }

    public function createInImgBan(Request $request, int $const)
    {
        $resizeImage= $this->compressImageIfLarge($const, $request->file('img'));
        $img = $resizeImage['path'];
        $image = __DIR__ . "/../../public/" . $img;
        $uploadedFile = $this->getFile($image);
        $imgPath = $this->imgBanRepository->upload($uploadedFile);
        unlink($image);

        return $imgPath;
    }

    /**
     * @param string $file
     * @return UploadedFile
     */
    public function getFile(string $file): UploadedFile
    {
        return new UploadedFile(
            $file, // полный путь к файлу
            basename($file), // имя файла
            mime_content_type($file), // MIME‑тип
            null, // размер (можно оставить null — определится автоматически)
            true // флаг валидности файла
        );
    }

    public function compressImageIfLarge(int $const, UploadedFile $file): array
    {
        $path = 'resized/';
        $quality = 98;

        try {
            // Проверяем валидность загруженного файла
            if (!$file->isValid()) {
                throw new \Exception('Файл не прошёл валидацию загрузки');
            }

            // Создаём менеджер с драйвером
            $manager = new ImageManager(new Driver());
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

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteById(int $id): bool
    {
        try {
            $imgSubj = $this->imgBanRepository->findById($id);
            if (!$imgSubj) {
                return false;
            }

            $this->imgBanRepository->delete($imgSubj->big_id);
            $this->imgBanRepository->delete($imgSubj->small_id);

            $result = ImgBanSubj::where('id', $id)->delete();
            return $result > 0;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@deleteById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@deleteById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }
}
