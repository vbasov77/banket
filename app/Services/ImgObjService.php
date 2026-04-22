<?php


namespace App\Services;


use App\Exceptions\VkApiException;
use App\Models\ImgObj;
use App\Repositories\ImgObjRepository;
use App\Repositories\KeyRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PharIo\Version\Exception;


class ImgObjService extends Service
{

    private KeyRepository $keyRepository;
    private ImgObjRepository $imgObjRepository;

    private VkService $vkService;

    public function __construct(ImgObjRepository $imgObjRepository,
                                VkService $vkService,
                                KeyRepository $keyRepository)
    {
        $this->keyRepository = $keyRepository;
        $this->vkService = $vkService;
        $this->imgObjRepository = $imgObjRepository;
    }


    public function imgObjStore(Request $request, int $objId): array
    {
        try {
            if (!empty($request->file('img'))) {
                $keyGo = $this->keyRepository->idGroupVkMaterial();

                try {
                    $photo = $this->vkService->createOneImgInVk($request, $keyGo);
                } catch (\Exception $e) {
                    throw new VkApiException(
                        'Ошибка при загрузке изображения в VK: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }

                $data = [
                    'obj_id' => $objId,
                    'path' => $photo->orig_photo->url,
                    'photo_id' => $photo->id,
                ];

                // Выносим сохранение в репозиторий
                $id = $this->imgObjRepository->insertImgData($data);

                return [$photo->orig_photo->url, $id];
            }

            throw new \Exception('Файл изображения не найден в запросе');
        } catch (VkApiException $e) {
            Log::channel('error_file')->error(
                'Ошибка сервиса ImgObjService@imgObjStore: ' . $e->getMessage(),
                [
                    'obj_id' => $objId,
                    'exception_class' => get_class($e),
                    'vk_error_code' => $e->getErrorCode()
                ]
            );
            throw $e;
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
            $keyGo = $this->keyRepository->idGroupVkMaterial();
            $photo = $this->vkService->createOneImgInVk($request, $keyGo);

            $data = [
                'path' => $photo->orig_photo->url,
                'photo_id' => $photo->id,
            ];

            ImgObj::where('id', $id)->update($data);

            return $photo->orig_photo->url;
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