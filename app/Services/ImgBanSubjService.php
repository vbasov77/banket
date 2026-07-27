<?php


namespace App\Services;


use App\Models\ImgBanSubj;
use App\Models\ImgObj;
use App\Repositories\ImgBanRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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
        $resizeImage = $this->compressImageIfLarge($const, $request->file('img'));
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
        $quality = 97;
        $watermarkText = "FeastBoom.ru";
        $fontPath = public_path('fonts/FredokaOneCyrillic-Regular.ttf');

        try {
            if (!$file->isValid()) {
                throw new \Exception('Файл не прошёл валидацию загрузки');
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getPathname());

            // Ресайз
            if ($image->width() > $const) {
                $newWidth = $const;
                $newHeight = (int)($newWidth * $image->height() / $image->width());
                $image->resize($newWidth, $newHeight);
            }

            $hasFont = file_exists($fontPath);

            if ($hasFont && $const >= 900) {
                // Для JPG используем контрастный контур:
                // Вариант: чёрная обводка + белая заливка — читается почти на любом фоне.
                // Если хочешь наоборот (белый контур на тёмном фоне) — поменяй цвета ниже.

                $image->text(
                    $watermarkText,
                    $image->width() - 90,
                    $image->height() - 30,
                    function ($font) use ($fontPath) {
                        $font->file($fontPath);
                        $font->size(15);

                        // Основной цвет текста (заливка) — белый
                        $font->color('#ffffff');

                        // Обводка (контур) — чёрная, толщина 2px
                        $font->stroke('#000000', 1);

                        $font->align('right');
                        $font->valign('bottom');
                    }
                );

                // Принудительно JPG — прозрачность не нужна, контур реализован цветом
                $format = 'jpg';
            } else {

                $format = strtolower($file->extension());
                if (!in_array($format, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $format = 'jpg';
                }
            }


            // Кодирование
            if (in_array($format, ['jpg', 'jpeg'])) {
                $encodedImage = $image->toJpeg($quality);
                $format = 'jpg';
            } elseif ($format === 'png') {
                $encodedImage = $image->toPng(6);
            } elseif ($format === 'webp') {
                $encodedImage = $image->toWebp($quality);
            } else {
                $encodedImage = $image->toJpeg($quality);
                $format = 'jpg';
            }

            // Сохранение
            $filename = md5(uniqid() . $file->getClientOriginalName()) . '.' . $format;
            $fullPath = $path . $filename;
            $publicFullPath = public_path($fullPath);

            $directory = dirname($publicFullPath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new \Exception('Не удалось создать директорию: ' . $directory);
                }
            }

            $bytesWritten = file_put_contents($publicFullPath, (string)$encodedImage);
            if ($bytesWritten === false) {
                throw new \Exception('Ошибка записи файла на диск');
            }

            $fileSize = filesize($publicFullPath);
            if ($fileSize === false) {
                throw new \Exception('Не удалось получить размер файла');
            }

            return [
                'success' => true,
                'path' => $fullPath,
                'size' => $fileSize,
                'error' => null,
            ];
        } catch (\InvalidArgumentException $e) {
            Log::channel('error_file')->error('Invalid image format: ' . $e->getMessage(), [
                'file_name' => $file->getClientOriginalName(),
            ]);
            return ['success' => false, 'path' => null, 'size' => null, 'error' => 'Неподдерживаемый формат изображения'];
        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error('Image processing error: ' . $e->getMessage(), [
                'file_name' => $file->getClientOriginalName(),
            ]);
            return ['success' => false, 'path' => null, 'size' => null, 'error' => 'Ошибка обработки изображения'];
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error: ' . $e->getMessage(), [
                'file_name' => $file->getClientOriginalName() ?? 'unknown',
            ]);
            return ['success' => false, 'path' => null, 'size' => null, 'error' => $e->getMessage()];
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

    public function destroyWithProfile(int $userId)
    {
        $ids = collect();

        // photo_id из img_obj
        $ids->push(
            ImgObj::whereHas('obj', fn($q) => $q->where('user_id', $userId))
                ->pluck('photo_id')
        );
        // big_id из img_ban_subj
        $ids->push(
            ImgBanSubj::whereHas('subject.obj', fn($q) => $q->where('user_id', $userId))
                ->whereNotNull('big_id')
                ->pluck('big_id')
        );

        // small_id из img_ban_subj
        $ids->push(
            ImgBanSubj::whereHas('subject.obj', fn($q) => $q->where('user_id', $userId))
                ->whereNotNull('small_id')
                ->pluck('small_id')
        );

        $photoIds = $ids->flatten()->filter()->toArray();

        $total = count($photoIds);

        for ($i = 0; $i < $total; $i++) {
            $id = $photoIds[$i];

            // (опционально) пауза, чтобы не упереться в лимиты API
            if ($i > 0 && $i % 10 === 0) {
                usleep(200000); // 0.2 сек каждые 10 запросов
            }

            $this->imgBanRepository->delete($id);
        }

    }

    public function getAllImageBanIds()
    {
        $imagebanConfig = config('services.imageban');

        if (empty($imagebanConfig['client_secret'])) {
            Log::channel('error_file')->error('CLIENT_ID не найден в конфигурации (services.php)');
            dd('Ошибка: CLIENT_ID не настроен в config/services.php');
        }

        $allIds = [];
        $page = 1;
        $limit = 50; // ImageBan отдаёт по 50 записей на страницу

        try {
            do {
                $response = Http::withHeaders([
                    'Authorization' => 'TOKEN ' . $imagebanConfig['client_secret'],
                ])->get("https://api.imageban.ru/v1/account/me/images/ids/{$page}");

                if (!$response->successful()) {
                    Log::channel('error_file')->error('HTTP ошибка при получении ID', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    dd('HTTP ошибка от API: ' . $response->status() . ' - ' . $response->body());
                }

                $data = $response->json();

                // Проверка структуры ответа
                if (!isset($data['success']) || $data['success'] !== true || !isset($data['data'])) {
                    $msg = $data['error']['message'] ?? 'Неизвестная ошибка ответа API';
                    Log::channel('error_file')->error('Ошибка логики API при получении ID', ['message' => $msg]);
                    dd('Ошибка API: ' . $msg);
                }

                $idsPage = $data['data'];

                if (empty($idsPage)) {
                    break; // Больше страниц нет
                }

                $allIds = array_merge($allIds, $idsPage);

                // Если пришло меньше лимита — значит, это последняя страница
                if (count($idsPage) < $limit) {
                    break;
                }

                $page++;

                // ВАЖНО: Небольшая пауза, чтобы не упереться в rate limit при большом количестве фото
                usleep(200000); // 0.2 секунды между запросами

            } while (true);

            // Вывод результата для проверки

            return $allIds;

        } catch (\Exception $e) {
            Log::channel('error_file')->error('Исключение при сборе ID фото', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }


}
