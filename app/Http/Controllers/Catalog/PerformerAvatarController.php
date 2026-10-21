<?php

namespace App\Http\Controllers\Catalog;

use App\Exceptions\VkApiException;
use App\Http\Controllers\Controller;
use App\Services\PerformerAvaService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PharIo\Version\Exception;

class PerformerAvatarController extends Controller
{

    public function __construct(private PerformerAvaService $performerAvaService)
    {
    }

    public function create(Request $request): Application|Factory|View|Response
    {
        try {
            $id = (int) $request->id;

            return view('catalog.ava.create', ['id' => $id]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в FavoriteController@edit: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'obj_id' => $id ?? 'unknown',
                    'exception_class' => get_class($e),

                    'ip' => $request->ip()
                ]
            );
            return response()->view('errors.500', [], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $file = $request->file('img');
            // Валидация файла
            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Файл изображения не предоставлен',
                    'errors' => ['img' => 'Изображение обязательно для загрузки']
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'img' => 'required|image|mimes:jpeg,png,jpg,gif|max:3072',
                'id' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации данных',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Обработка изображения через сервис
            $data = $this->performerAvaService->performerAvaStore($request, $request->id);

            // Предполагаем, что сервис возвращает [path, id] или выбрасывает исключение
            [$path, $id] = $data;

            return response()->json([
                'success' => true,
                'message' => 'Изображение успешно загружено',
                'data' => [
                    'path' => $path,
                    'id' => $id,
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (FileNotFoundException $e) {
            Log::channel('error_file')->error(
                'Ошибка файловой системы при загрузке изображения: ' . $e->getMessage(),
                [
                    'input_data' => $request->except(['img']),
                    'file_name' => $request->file('img')?->getClientOriginalName(),
                    'exception_class' => get_class($e),

                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'success' => false,
                'message' => 'Не удалось сохранить файл на сервере',
                'error_code' => 'FILE_SYSTEM_ERROR'
            ], 500);
        } catch (VkApiException $e) {
            Log::channel('error_file')->error(
                'Ошибка API ВКонтакте при загрузке изображения: ' . $e->getMessage(),
                [
                    'obj_id' => $request->id,
                    'exception_class' => get_class($e),
                    'vk_error_code' => $e->getErrorCode(),

                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при работе с API ВКонтакте',
                'error_code' => 'VK_API_ERROR'
            ], 500);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка при сохранении изображения: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'obj_id' => $request->id,
                    'exception_class' => get_class($e),
                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении данных в базу',
                'error_code' => 'DATABASE_ERROR'
            ], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка при загрузке изображения: ' . $e->getMessage(),
                [
                    'input_data' => $request->except(['img']),
                    'obj_id' => $request->id ?? 'unknown',
                    'exception_class' => get_class($e),

                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'success' => false,
                'message' => 'Произошла непредвиденная ошибка',
                'error_code' => 'UNEXPECTED_ERROR'
            ], 500);
        }
    }

}
