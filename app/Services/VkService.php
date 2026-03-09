<?php


namespace App\Services;


use App\Repositories\KeyRepository;
use App\Repositories\RequestRepository;
use Illuminate\Http\File;
use Illuminate\Http\Request;


class VkService extends Service
{
    private $requestRepository;
    private $keyRepository;
    private $fileService;
    private $imgService;

    public function __construct()
    {
        $this->requestRepository = new RequestRepository();
        $this->keyRepository = new KeyRepository();
        $this->fileService = new FileService();
        $this->imgService = new ImageService();


    }

    public function createOneImgInVk(Request $request, int $groupId)
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
                    return ['error' => $json];
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
    public function server(int $groupId, string $accessToken)
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

    function getClosestSize(string $originalUrl, int $minWidth, int $maxWidth, int $quality = 96): string
    {
        // Шаг 1. Ищем все размеры в URL (шаблон NxM, где N и M — числа)
        preg_match_all('/(\d+)x(\d+)/', $originalUrl, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            // Если размеров не найдено — возвращаем оригинал с новым качеством
            return preg_replace('/(&quality=\d+)?/', "&quality=$quality", $originalUrl);
        }

        // Шаг 2. Собираем все найденные ширины
        $widths = array_map(function ($match) {
            return (int)$match[1]; // Первая группа — ширина
        }, $matches);

        // Шаг 3. Фильтруем ширины по диапазону [minWidth, maxWidth]
        $validWidths = array_filter($widths, function ($width) use ($minWidth, $maxWidth) {
            return $width >= $minWidth && $width <= $maxWidth;
        });

        if (empty($validWidths)) {
            // Если в диапазоне нет ни одного размера — берём ближайший:
            // - если все ширины < minWidth → берём максимальную из найденных;
            // - если все ширины > maxWidth → берём минимальную из найденных.
            $closestWidth = min(array_map(function ($width) use ($minWidth, $maxWidth) {
                if ($width < $minWidth) {
                    return $minWidth - $width;
                }
                if ($width > $maxWidth) {
                    return $width - $maxWidth;
                }
                return 0;
            }, $widths));

            // Находим фактическую ширину, соответствующую ближайшему отклонению
            $validWidths = [$widths[array_search($closestWidth, array_map(function ($w) use ($minWidth, $maxWidth) {
                return $w < $minWidth ? $minWidth - $w : ($w > $maxWidth ? $w - $maxWidth : 0);
            }, $widths))]];
        }

        // Шаг 4. Выбираем минимальную подходящую ширину (чтобы не переплачивать за пиксели)
        $targetWidth = min($validWidths);

        // Шаг 5. Находим соответствующую высоту для этого размера
        $targetHeight = null;
        foreach ($matches as $match) {
            if ((int)$match[1] === $targetWidth) {
                $targetHeight = (int)$match[2];
                break;
            }
        }

        // Шаг 6. Формируем новую строку размера
        $newSize = "{$targetWidth}x{$targetHeight}";

        // Шаг 7. Заменяем все размеры в URL на выбранный и обновляем качество
        $modifiedUrl = preg_replace('/\d+x\d+/', $newSize, $originalUrl);
        $modifiedUrl = preg_replace('/(&quality=\d+)?/', "&quality=$quality", $modifiedUrl);

        return $modifiedUrl;
    }


}