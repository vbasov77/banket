<?php

declare(strict_types=1);

namespace App\Repositories;


use App\Models\ImgBanSubj;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ImgBanRepository extends Repository
{
    public function upload(UploadedFile $imageFile)
    {
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
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);

            Session::flash('error', 'Произошла ошибка: ' . $e->getMessage());
        }

        return redirect()->route('test');
    }

    /**
     * @param int $subjId
     * @return int
     * @throws \Exception
     */
    public function getNextPosition(int $subjId): int
    {
        try {
            return ImgBanSubj::where('subj_id', $subjId)->count() + 1;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@getNextPosition: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $subjId,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@getNextPosition: ' . $e->getMessage(),
                [
                    'subj_id' => $subjId,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function findImgBySubjId(int $id): mixed
    {
        try {
            return ImgBanSubj::where('subj_id', $id)
                ->orderBy('position', 'asc')
                ->get();
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@findImgByObjId: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@findImgByObjId: ' . $e->getMessage(),
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
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function findById(int $id): mixed
    {
        try {
            return ImgBanSubj::find($id);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в ImgSubjRepository@findById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в ImgSubjRepository@findById: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    public function delete(string $imageId)
    {
        $imagebanConfig = config('services.imageban');

        $url = 'https://api.imageban.ru/v1/image/delete/' . $imageId;

        Http::withHeaders([
            'Authorization' => 'Bearer ' . $imagebanConfig['client_secret'],
        ])->delete($url);

    }

}