<?php

namespace App\Repositories;

use App\Models\ImgObj;
use App\Models\PerformerAvatar;
use App\Models\PerformerPackage;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PerformerAvaRepository
{
    public function findImgByObjId(int $id)
    {
        return PerformerAvatar::where('performer_id', $id)->first();
    }

    public function upload(UploadedFile $imageFile)
    {
        $originalFileName = $imageFile->getClientOriginalName();

        try {
            $imagebanConfig = config('services.imageban');

            if (empty($imagebanConfig['client_secret'])) {
                throw new \Exception('CLIENT_ID не найден в конфигурации (services.php)');
            }

            // Проверка доступности файла
            if (!is_readable($imageFile->getRealPath())) {
                throw new \Exception('Файл не доступен для чтения: ' . $imageFile->getRealPath());
            }

            $response = Http::withHeaders([
                'Authorization' => 'TOKEN ' . $imagebanConfig['client_secret'],
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
                    // Добавляем запись в базу данных
                    return [$data['data']['id'], $data['data']['link']] ?? 'URL не найден';
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
                'timestamp' => now()->toDateTimeString()
            ]);

            Session::flash('error', 'Произошла ошибка: ' . $e->getMessage());
        }

        return redirect()->route('test');
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function insertImgData(array $data): int
    {
        try {
            $avatar = PerformerAvatar::updateOrCreate(
                ['performer_id' => $data['performer_id']],
                [
                    'small_img' => $data['small_img'],
                    'small_id'  => $data['small_id'],
                ]
            );

            return (int) $avatar->id;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgObjRepository@insertImgData: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'data' => $data,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgObjRepository@insertImgData: ' . $e->getMessage(),
                [
                    'data' => $data,
                    'exception_class' => get_class($e),

                ]
            );
            throw $e;
        }
    }
}
