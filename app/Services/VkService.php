<?php


namespace App\Services;


use App\Models\AddressSubj;
use App\Repositories\KeyRepository;
use App\Repositories\RequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class VkService extends Service
{
    private RequestRepository $requestRepository;
    private KeyRepository $keyRepository;
    private ImageService $imgService;

    public function __construct(RequestRepository $requestRepository, KeyRepository $keyRepository, ImageService $imgService)
    {
        $this->requestRepository = $requestRepository;
        $this->keyRepository = $keyRepository;
        $this->imgService = $imgService;
    }

    /**
     * @param Request $request
     * @param int $groupId
     * @return object|null
     */
    public function createOneImgInVk(Request $request, int $groupId, int $albumId): ?object
    {
        $accessToken = $this->keyRepository->accessToken();
        $server = $this->server($groupId, $accessToken, $albumId);
        if (!empty($request->file('img'))) {
            sleep(0.5);
            $resizeImage = $this->imgService->compressImageIfLarge($request->file('img'));
            $img = $resizeImage['path'];
            $image = __DIR__ . "/../../public/" . $img;
            $uploadUrl = $server->response->upload_url;

            if (!empty($uploadUrl)) {
                // Отправка изображения на сервер
                if (function_exists('curl_file_create')) {
                    $curlFile = curl_file_create($image, 'image/jpeg', 'image.jpg');
                } else {
                    $curlFile = '@' . $image;
                }

                $json = json_decode($this->requestRepository->postFile($uploadUrl, $curlFile), true);
                // Проверка на ошибки от VK API
                if (isset($json['error'])) {
                    Log::error('VK API error during image upload', [
                        'error' => $json['error'],
                        'upload_url' => $uploadUrl
                    ]);
                    unlink($image);
                    return null;
                }

                // Проверяем наличие обязательных полей в ответе
                if (empty($json['server']) || empty($json['photos_list']) || empty($json['hash'])) {
                    Log::error('Missing required fields in VK upload response', [
                        'received_data' => $json,
                        'upload_url' => $uploadUrl
                    ]);
                    unlink($image);
                    return null;
                }

                // Сохранение фото в группе
                $urlSaveWallPhoto = 'https://api.vk.com/method/photos.save';
                $dataSaveWallPhoto = [
                    'album_id' => $albumId,
                    'group_id' => $groupId,
                    'server' => $json['server'],
                    'photos_list' => $json['photos_list'], // используем правильное поле
                    'hash' => $json['hash'],
                    'access_token' => $accessToken,
                    'v' => 5.199
                ];

                $save = json_decode($this->requestRepository->post($urlSaveWallPhoto, $dataSaveWallPhoto));

                unlink($image); // Удаляем временный файл с сервера

                if ($save && isset($save->response) && !empty($save->response)) {
                    return $save->response[0];
                } else {
                    Log::error('Failed to save photo in VK group', [
                        'save_response' => $save,
                        'data_sent' => $dataSaveWallPhoto
                    ]);
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @param int $groupId
     * @param string $accessToken
     * @return mixed
     */
    public function server(int $groupId, string $accessToken, int $albumId): mixed
    {
        // Получение сервера vk для загрузки изображения.
        $urlGetWallUploadServer = 'https://api.vk.com/method/photos.getUploadServer';
        $data = [
            'album_id' => $albumId,
            'group_id' => $groupId,
            'access_token' => $accessToken,
            'v' => 5.199
        ];

        return json_decode($this->requestRepository->post($urlGetWallUploadServer, $data));
    }

    public function deleteImg(int $groupId, int $imgId, string $accessToken)
    {
        $url = 'https://api.vk.com/method/photos.delete';
        $data = [
            'photo_id' => $imgId,
            'owner_id' => '-' . $groupId,
            'access_token' => $accessToken,
            'v' => 5.199
        ];
        return json_decode($this->requestRepository->post($url, $data));
    }

}