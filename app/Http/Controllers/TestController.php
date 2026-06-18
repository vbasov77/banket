<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ImgBanSubj;
use App\Models\UserVk;
use App\Repositories\KeyRepository;
use App\Requests\VkRequests;
use App\Services\ImgObjService;
use App\Services\ImgSubjService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class TestController extends Controller
{
    protected ImgObjService $imgObjService;

    protected ImgSubjService $imgSubjService;

    protected VkRequests $vkRequests;

    protected KeyRepository $keyRepository;

    public function __construct(ImgObjService  $imgObjService,
                                ImgSubjService $imgSubjService,
                                VkRequests     $vkRequests,
                                KeyRepository  $keyRepository)
    {
        $this->imgObjService = $imgObjService;
        $this->imgSubjService = $imgSubjService;
        $this->vkRequests = $vkRequests;
        $this->keyRepository = $keyRepository;
    }


    public function show()
    {
        return view('tests.img');
    }

    public function test()
    {
        return view('tests.test');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // до 10 МБ
        ]);

        $imageFile = $request->file('image');
        $originalFileName = $imageFile->getClientOriginalName();

        try {
            $imagebanConfig = config('services.imageban');

            if (empty($imagebanConfig['client_id'])) {
                throw new \Exception('CLIENT_ID не найден в конфигурации (services.php)');
            }

            // Проверка доступности файла
            if (!is_readable($imageFile->getRealPath())) {
                throw new \Exception('Файл не доступен для чтения: ' . $imageFile->getRealPath());
            }

            $response = Http::withHeaders([
                'Authorization' => 'TOKEN ' . $imagebanConfig['client_id'],
            ])->attach(
                'image',
                file_get_contents($imageFile->getRealPath()),
                $originalFileName
            )->post('https://api.imageban.ru/v1', [
                'name' => $originalFileName // обязательное поле по документации
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] === true) {
                    // Извлекаем ID изображения из ответа API
                    $imageId = $data['data']['id'] ?? 'ID не найден';
                    // Извлекаем URL изображения
                    $imageUrl = $data['data']['link'] ?? 'URL не найден';

                    // Добавляем запись в базу данных
                    $imgBanSubj = new ImgBanSubj();
                    $imgBanSubj->subj_id = $request->input('subj_id', 1); // Получаем subj_id из запроса, если не передан — ставим 1
                    $imgBanSubj->img_id = $imageId;
                    $imgBanSubj->path = $imageUrl;
                    $imgBanSubj->save();

                    Log::channel('info_file')->info('Запись добавлена в таблицу img_ban_subj', [
                        'subj_id' => $imgBanSubj->subj_id,
                        'img_id' => $imgBanSubj->img_id,
                        'record_id' => $imgBanSubj->id,
                    ]);

                } else {
                    // Извлекаем код и сообщение ошибки из ответа API
                    $errorMessage = $data['error']['message'] ?? 'неизвестная ошибка';
                    $errorCode = $data['error']['code'] ?? 'N/A';

                    Log::channel('error_file')->error('Ошибка от imageban.ru API', [
                        'filename' => $originalFileName,
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'response_data' => $data,
                    ]);

                    Session::flash('error', 'Ошибка imageban.ru (код ' . $errorCode . '): ' . $errorMessage);
                }
            } else {
                Log::channel('error_file')->error('HTTP‑ошибка при загрузке на imageban.ru', [
                    'filename' => $originalFileName,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'timestamp' => now()->toDateTimeString()
                ]);

                Session::flash('error', 'HTTP‑ошибка: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Исключение при загрузке изображения', [
                'filename' => $originalFileName,
                'exception_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);

            Session::flash('error', 'Произошла ошибка: ' . $e->getMessage());
        }

        return redirect()->route('test');
    }

    public function testCities()
    {
        return view('tests.test_cities');
    }

    public function store(Request $request)
    {


    }

    public function delete(Request $request)
    {
        $imageId = 'xdPd0HF';

        $imagebanConfig = config('services.imageban');

        $url = 'https://api.imageban.ru/v1/image/delete/' . $imageId;

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $imagebanConfig['client_secret'],
        ])->delete($url);

        return redirect()->route('test');
    }


}