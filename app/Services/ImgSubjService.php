<?php


namespace App\Services;


use App\Exceptions\VkApiException;
use App\Models\ImgObj;
use App\Models\ImgSubj;
use App\Repositories\ImgSubjRepository;
use App\Repositories\KeyRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ImgSubjService extends Service
{

    private KeyRepository $keyRepository;
    private ImgSubjRepository $imgSubjRepository;
    private VkService $vkService;

    /**
     * @param KeyRepository $keyRepository
     * @param ImgSubjRepository $imgSubjRepository
     * @param VkService $vkService
     */
    public function __construct(KeyRepository     $keyRepository,
                                ImgSubjRepository $imgSubjRepository,
                                VkService         $vkService)
    {
        $this->keyRepository = $keyRepository;
        $this->vkService = $vkService;
        $this->imgSubjRepository = $imgSubjRepository;
    }


    /**
     * @throws VkApiException
     */
    public function ImgSubjStore(Request $request, int $id, int $groupId, int $albumId): array
    {
        try {
            if (!$request->hasFile('img')) {
                throw new \Exception('Файл изображения отсутствует в запросе');
            }

            try {
                $photo = $this->vkService->createOneImgInVk($request, $groupId, $albumId);
                if (!$photo) {
                    throw new VkApiException('Не удалось загрузить изображение в VK', 0);
                }
            } catch (\Exception $e) {
                throw new VkApiException(
                    'Ошибка при загрузке изображения в VK: ' . $e->getMessage(),
                    0,
                    (array)$e
                );
            }

            $position = $this->imgSubjRepository->getNextPosition($id);

            $data = [
                'subj_id' => $id,
                'path' => $photo->orig_photo->url,
                'photo_id' => $photo->id,
                'group_id' => $groupId,
                'position' => $position,
            ];

            $newId = $this->imgSubjRepository->insertImgData($data);

            return [$photo->orig_photo->url, $newId];
        } catch (VkApiException $e) {
            Log::channel('error_file')->error(
                'Ошибка сервиса ImgSubjService@ImgSubjStore (VK API): ' . $e->getMessage(),
                [
                    'subj_id' => $id,
                    'vk_error_code' => $e->getErrorCode(),
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjService@ImgSubjStore: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $id
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjService@ImgSubjStore: ' . $e->getMessage(),
                [
                    'subj_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * Получение фото из базы
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function findImgByObjId(int $id): mixed
    {
        try {
            return $this->imgSubjRepository->findImgByObjId($id);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjService@findImgByObjId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $id
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjService@findImgByObjId: ' . $e->getMessage(),
                [
                    'subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

    /**
     * Метод по изменению позиций фото в фотоальбоме(сервис)
     * @param array $orderData
     * @return void
     * @throws \Exception
     */
    public function updateImageOrder(array $orderData): void
    {
        try {
            $this->imgSubjRepository->updateImagePositions($orderData);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjService@updateImageOrder: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'order_data_count' => count($orderData),
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjService@updateImageOrder: ' . $e->getMessage(),
                [
                    'order_data_count' => count($orderData),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteImgSubj(int $id): bool
    {
        try {
            $imgSubj = $this->imgSubjRepository->findById($id);
            if (!$imgSubj) {
                return false;
            }
            $accessToken = $this->keyRepository->accessToken();

            $bool = $this->vkService->deleteImg($imgSubj->group_id, $imgSubj->photo_id, $accessToken);

            if(!empty($bool->response) == 1){
                return $this->imgSubjRepository->deleteById($id);
            }

            Log::channel('error_file')->error('bool', [$bool]);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjService@deleteImgSubj: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjService@deleteImgSubj: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    public function destroy()
    {

    }
}