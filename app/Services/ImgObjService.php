<?php


namespace App\Services;


use App\Exceptions\VkApiException;
use App\Models\ImgObj;
use App\Repositories\ImgBanRepository;
use App\Repositories\ImgObjRepository;
use App\Repositories\KeyRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PharIo\Version\Exception;


class ImgObjService extends Service
{
    private ImgObjRepository $imgObjRepository;

    protected ImgBanSubjService $imgBanSubjService;

    protected ImgBanRepository $imgBanRepository;

    public function __construct(ImgObjRepository  $imgObjRepository,
                                ImgBanSubjService $imgBanSubjService,
                                ImgBanRepository  $imgBanRepository)
    {
        $this->imgObjRepository = $imgObjRepository;
        $this->imgBanSubjService = $imgBanSubjService;
        $this->imgBanRepository = $imgBanRepository;
    }


    public function imgObjStore(Request $request, int $objId): array
    {
        try {
            if (!empty($request->file('img'))) {
                try {
                    $photo = $this->imgBanSubjService->createInImgBan($request, 360);
                } catch (\Exception $e) {
                    // Передаём массив в $vkErrorDetails, а не объект Exception
                }

                $data = [
                    'obj_id' => $objId,
                    'path' => $photo[1],
                    'photo_id' => $photo[0],
                ];

                // Выносим сохранение в репозиторий
                $id = $this->imgObjRepository->insertImgData($data);

                return [$photo[1], $id];
            }

            throw new \Exception('Файл изображения не найден в запросе');
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgObjService@imgObjStore: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'obj_id' => $objId
                ]
            );
            throw $e;
        } catch (Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgObjService@imgObjStore: ' . $e->getMessage(),
                [
                    'obj_id' => $objId,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }


    public function imgObjUpdate(Request $request, int $id)
    {
        if (!empty($request->file('img'))) {
            try {
                $oldImg = $this->imgObjRepository->findImgById($id);
                $photo = $this->imgBanSubjService->createInImgBan($request, 360);
                $data = [
                    'path' => $photo[1],
                    'photo_id' => $photo[0],
                ];

                ImgObj::where('id', $id)->update($data);

                $this->imgBanRepository->delete($oldImg[0]->photo_id);

                return $photo[1];
            } catch (\Exception $e) {
                Log::channel('error_file')->error('Ошибка сохранения/удаления  фото объекта', [$e]);
            }

        }
    }

    /**
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function findImgByObjId(int $id): mixed
    {
        try {
            return $this->imgObjRepository->findImgByObjId($id);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgObjService@findImgByObjId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'obj_id' => $id
                ]
            );
            throw $e;
        } catch (Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgObjService@findImgByObjId: ' . $e->getMessage(),
                [
                    'obj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

}