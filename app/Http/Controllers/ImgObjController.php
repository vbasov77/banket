<?php

namespace App\Http\Controllers;


use App\Exceptions\VkApiException;
use App\Models\ImgObj;
use App\Services\ImgObjService;
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

class ImgObjController extends Controller
{
    private ImgObjService $imgObjService;


    public function __construct(ImgObjService $imgObjService)
    {
        $this->imgObjService = $imgObjService;
    }


    /**
     * @param Request $request
     * @return Application|Factory|View|Response
     * @throws Exception
     */
    public function edit(Request $request): Application|Factory|View|Response
    {
        try {
            $id = $request->id;

            if (!$id) {
                Log::channel('error_file')->error(
                    'Отсутствует ID объекта в FavoriteController@edit',
                    [
                        'request_data' => $request->all(),
                        'ip' => $request->ip()
                    ]
                );
                return response()->view('errors.400', [], 400);
            }

            try {
                $img = $this->imgObjService->findImgByObjId($id);
            } catch (\Exception $e) {
                Log::channel('error_file')->error(
                    'Ошибка при вызове сервиса в FavoriteController@edit: ' . $e->getMessage(),
                    [
                        'obj_id' => $id,
                        'exception_class' => get_class($e),
                        'trace' => $e->getTraceAsString(),
                        'ip' => $request->ip()
                    ]
                );
                throw $e; // Перебрасываем исключение для обработки основным блоком catch
            }

            if ($img) {
                return view('img_obj.edit', ['img' => $img]);
            } else {
                return view('img_obj.create', ['id' => $id]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в FavoriteController@edit: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'obj_id' => $id ?? 'unknown',
                    'ip' => $request->ip()
                ]
            );
            return response()->view('errors.500', [], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в FavoriteController@edit: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'obj_id' => $id ?? 'unknown',
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
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
            $data = $this->imgObjService->imgObjStore($request, $request->id);

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
                    'trace' => $e->getTraceAsString(),
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
                    'trace' => $e->getTraceAsString(),
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
                    'trace' => $e->getTraceAsString(),
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
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
                'img' => 'required|image|mimes:jpeg,png,jpg,gif|max:3072', // до 10 МБ
                'id' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации данных',
                    'errors' => $validator->errors()
                ], 422);
            }

            $id = (int)$request->id;
            // Обработка изображения через сервис
            $path = $this->imgObjService->imgObjUpdate($request, $id);

            // Предполагаем, что сервис возвращает [path, id] или выбрасывает исключение

            return response()->json([
                'success' => true,
                'message' => 'Изображение успешно загружено',
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (FileNotFoundException $e) {
            Log::error('Ошибка файловой системы при загрузке изображения: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Не удалось сохранить файл на сервере',
                'error_code' => 'FILE_SYSTEM_ERROR'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Неожиданная ошибка при загрузке изображения: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла непредвиденная ошибка',
                'error_code' => 'UNEXPECTED_ERROR'
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        // 1. Валидация ID
        if (!is_numeric($request->id) || $request->id <= 0) {
            return response()->json([
                'error' => 'Некорректный ID',
                'answer' => 'error'
            ], 400); // Bad Request
        }

        try {
            // 2. Проверка существования записи
            $imgObj = ImgObj::find($request->id);

            if (!$imgObj) {
                return response()->json([
                    'error' => 'Запись не найдена',
                    'answer' => 'not_found'
                ], 404); // Not Found
            }

            // 3. Удаление
            $imgObj->delete();

            // 4. Успешный ответ с деталями
            return response()->json([
                'success' => true,
                'message' => 'Запись удалена',
                'deleted_id' => $request->id,
                'answer' => 'ok'
            ], 200);

        } catch (\Exception $e) {
            // 5. Логгирование ошибки
            Log::error('Ошибка при удалении ImgObj: ' . $e->getMessage());

            return response()->json([
                'error' => 'Произошла ошибка на сервере',
                'answer' => 'error',
                'debug' => $e->getMessage() // Только в режиме отладки!
            ], 500); // Internal Server Error
        }
    }
}
