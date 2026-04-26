<?php


namespace App\Services;


use App\Repositories\KeyRepository;
use App\Repositories\RequestRepository;
use Illuminate\Http\File;
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
    public function createOneImgInVk(Request $request, int $groupId): ?object
    {
        $accessToken = $this->keyRepository->accessToken();

        $server = $this->server($groupId, $accessToken);

        if (!empty($request->file('img'))) {
            sleep(0.5);
            $resizeImage = $this->imgService->compressImageIfLarge($request->file('img'));
            $img = $resizeImage['path'];  // Записываем временный файл на сервер
            $image = __DIR__ . "/../../public/" . $img;

            if (!empty($server->response->upload_url)) {
                // Отправка изображения на сервер.
                if (function_exists('curl_file_create')) {
                    $curlFile = curl_file_create($image, 'image/jpeg', 'image.jpg');
                } else {
                    $curlFile = '@' . $image;
                }

                $json = json_decode($this->requestRepository->postFile($server->response->upload_url, $curlFile), true);

                if ($json) {
                    // Сохранение фото в группе.
                    $urlSaveWallPhoto = 'https://api.vk.com/method/photos.saveWallPhoto';
                    $dataSaveWallPhoto = [
                        'group_id' => $groupId,
                        'server' => $json['server'],
                        'photo' => stripslashes($json['photo']),
                        'hash' => $json['hash'],
                        'access_token' => $accessToken,
                        'v' => 5.131
                    ];
                    $save = json_decode($this->requestRepository->post($urlSaveWallPhoto, $dataSaveWallPhoto));

                    unlink($image); // Удаляем временный файл с сервера

                    if ($save) {
                        return $save->response[0];
                    }
                } else {
                    return (object)['error' => $json];
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
    public function server(int $groupId, string $accessToken): mixed
    {
        // Получение сервера vk для загрузки изображения.
        $urlGetWallUploadServer = 'https://api.vk.com/method/photos.getWallUploadServer';
        $data = [
            'group_id' => $groupId,
            'access_token' => $accessToken,
            'v' => 5.131
        ];

        return json_decode($this->requestRepository->post($urlGetWallUploadServer, $data));
    }


}