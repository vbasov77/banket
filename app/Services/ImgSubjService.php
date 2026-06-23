<?php


namespace App\Services;


use App\Exceptions\VkApiException;
use App\Models\ImgBanSubj;
use App\Models\ImgObj;
use App\Models\ImgSubj;
use App\Repositories\ImgBanRepository;
use App\Repositories\ImgSubjRepository;
use App\Repositories\KeyRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ImgSubjService extends Service
{

    protected ImgBanSubjService $imgBanSubjService;

    protected ImgBanRepository $imgBanRepository;

    protected ImgSubjRepository $imgSubjRepository;

    /**
     * @param ImgBanSubjService $imgBanSubjService
     */
    public function __construct(ImgBanSubjService $imgBanSubjService, ImgBanRepository $imgBanRepository,
                                ImgSubjRepository $imgSubjRepository)
    {
        $this->imgBanSubjService = $imgBanSubjService;
        $this->imgBanRepository = $imgBanRepository;
        $this->imgSubjRepository = $imgSubjRepository;
    }


    public function ImgSubjStore(Request $request, int $id)
    {
        try {
            if (!$request->hasFile('img')) {
                throw new \Exception('Файл изображения отсутствует в запросе');
            }

            try {

                $photoBig = $this->imgBanSubjService->createInImgBan($request, 900);
                $smallPhoto = $this->imgBanSubjService->createInImgBan($request, 360);

            } catch (\Exception $e) {
                throw new VkApiException(
                    'Ошибка при загрузке изображения в VK: ' . $e->getMessage(),
                    0,
                    (array)$e
                );
            }
            $position = $this->imgBanRepository->getNextPosition($id);
            Log::channel('info_file')->info('data', [$photoBig[0], $smallPhoto[0]]);

            $data = [
                'subj_id' => $id,
                'big_id' => $photoBig[0],
                'big_img' => $photoBig[1],
                'small_id' => $smallPhoto[0],
                'small_img' => $smallPhoto[1],
                'position' => $position,

            ];
            $newImgId = ImgBanSubj::insertGetId($data);

            return [$smallPhoto[1], $newImgId];
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
    public function findImgBySubjId(int $id): mixed
    {
        try {
            return $this->imgBanRepository->findImgBySubjId($id);

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
            return $this->imgBanSubjService->deleteById($id);
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
}