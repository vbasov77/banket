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
    private ImgObjRepository $imgObjRepository;

    private VkService $vkService;

    public function __construct(ImgObjRepository $imgObjRepository,
                                VkService        $vkService)
    {
        $this->vkService = $vkService;
        $this->imgObjRepository = $imgObjRepository;
    }


    public function imgObjStore(Request $request, int $objId): array
    {
        try {
            if (!empty($request->file('img'))) {
                $groupId = 239358651;
                $albumId = 311175944;

                try {
                    $photo = $this->vkService->createOneImgInVk($request, $groupId, $albumId);
                } catch (\Exception $e) {
                    // Передаём массив в $vkErrorDetails, а не объект Exception
                    throw new VkApiException(
                        'Ошибка при загрузке изображения в VK: ' . $e->getMessage(),
                        0,
                        [
                            'original_exception_class' => get_class($e),
                            'original_message' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'input_data' => [
                                'obj_id' => $objId,
                                'group_id' => $groupId,
                                'album_id' => $albumId
                            ]
                        ],
                        $e // передаём как $previous для цепочки исключений
                    );
                }

                // Проверка на null после вызова VK сервиса
                if ($photo === null) {
                    throw new VkApiException(
                        'Не удалось загрузить изображение в VK — получен пустой ответ',
                        0,
                        [
                            'input_data' => [
                                'obj_id' => $objId,
                                'group_id' => $groupId,
                                'album_id' => $albumId
                            ]
                        ]
                    );
                }

                // Дополнительная проверка структуры ответа VK
                if (!isset($photo->orig_photo->url) || !isset($photo->id)) {
                    throw new VkApiException(
                        'Некорректный ответ от VK API — отсутствуют обязательные поля',
                        0,
                        [
                            'received_photo_data' => $photo,
                            'expected_fields' => ['orig_photo->url', 'id']
                        ]
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
                    'vk_error_code' => $e->getErrorCode(),
                    'vk_error_details' => $e->getErrorDetails()
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
            $groupId = 239358651;
            $albumId = 311175944;
            $photo = $this->vkService->createOneImgInVk($request, $groupId, $albumId);
            Log::channel('info_file')->info('photo', [$photo]);
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