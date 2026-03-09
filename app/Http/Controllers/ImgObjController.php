<?php

namespace App\Http\Controllers;


use App\Models\ImgObj;
use App\Services\ImageService;
use App\Services\ImgObjService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PharIo\Version\Exception;

class ImgObjController extends Controller
{
    private $imgObjService;
    private $imgService;

    public function __construct()
    {
        $this->imgObjService = new ImgObjService();
        $this->imgService = new ImageService();
    }


    public function edit(Request $request)
    {
        $id = $request->id;
        $img = $this->imgObjService->findImgByObjId($id);
        if ($img) {
            return view('img_obj.edit', ['img' => $img]);
        } else {
            return view('img_obj.create', ['id' => $id]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function imgOrderChange(Request $request): JsonResponse
    {
        $data = $request->input('order');
        foreach ($data as $index => $id) {
            DB::table('img_obj')->where('id', $id)->update(['position' => $index]);
        }

        return response()->json([
            'message' => 'Порядок изменён.',
            'alert-type' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
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
    public function destroy(Request $request)
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
