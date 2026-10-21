<?php


namespace App\Services;


use App\Exceptions\VkApiException;
use App\Models\ImgObj;
use App\Models\PerformerAvatar;
use App\Repositories\ImgBanRepository;
use App\Repositories\ImgObjRepository;
use App\Repositories\KeyRepository;
use App\Repositories\PerformerAvaRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PharIo\Version\Exception;


class PerformerAvaService extends Service
{

    public function __construct(private PerformerAvaRepository $performerAvaRepository,
                                private ImgBanSubjService      $imgBanSubjService,
                                private ImgBanRepository       $imgBanRepository)
    {
    }

    public function findImgByObjId(int $id)
    {
        return $this->performerAvaRepository->findImgByObjId($id);
    }

    public function createInImgBan(Request $request, int $const)
    {
        $resizeImage = $this->imgBanSubjService->compressImageIfLarge($const, $request->file('img'));
        $img = $resizeImage['path'];
        $image = __DIR__ . "/../../public/" . $img;
        $uploadedFile = $this->getFile($image);
        $imgPath = $this->performerAvaRepository->upload($uploadedFile);
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

    public function performerAvaStore(Request $request, int $performerId): array
    {
        try {
            if (!empty($request->file('img'))) {
                try {
                    $photo = $this->createInImgBan($request, 360);
                } catch (\Exception $e) {
                    // Передаём массив в $vkErrorDetails, а не объект Exception
                }

                $img = PerformerAvatar::where('performer_id', $performerId)->first();

                if ($img) {
                    $this->imgBanRepository->delete($img->small_id);
                }

                $data = [
                    'performer_id' => $performerId,
                    'small_img' => $photo[1],
                    'small_id' => $photo[0],
                    'user_id' => Auth::user()->id
                ];

                // Выносим сохранение в репозиторий
                $id = $this->performerAvaRepository->insertImgData($data);

                return [$photo[1], $id];
            }

            throw new \Exception('Файл изображения не найден в запросе');
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgObjService@imgObjStore: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'performer_id' => $performerId
                ]
            );
            throw $e;
        } catch (Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgObjService@imgObjStore: ' . $e->getMessage(),
                [
                    'performer_id' => $performerId,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }
}