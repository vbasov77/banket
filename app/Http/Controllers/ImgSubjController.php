<?php

namespace App\Http\Controllers;


use App\Exceptions\VkApiException;
use App\Models\AddressSubj;
use App\Models\AlbumsVk;
use App\Services\ImgSubjService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImgSubjController extends Controller
{
    private ImgSubjService $imgSubjService;


    /**
     * @param ImgSubjService $imgSubjService
     */
    public function __construct(ImgSubjService $imgSubjService)
    {
        $this->imgSubjService = $imgSubjService;

    }


    public function edit(Request $request)
    {
        $subj = $request->id;
        $address = AddressSubj::where('subj_id', $subj)->exists();
        if (!$address) {
            return redirect()->route('map.edit', ['id' => $subj, 'error' => 'Сначала добавьте адрес']);
        }

        try {
            if (!$subj) {
                Log::channel('error_file')->error(
                    'Отсутствует ID субъекта в Controller@edit',
                    [
                        'request_data' => $request->all(),
                        'ip' => $request->ip()
                    ]
                );
                return response()->view('errors.400', [], 400);
            }

            $images = $this->imgSubjService->findImgByObjId($subj);

            return view('img_subj.edit', ['subj' => $subj, 'images' => $images]);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в Controller@edit: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $subj ?? 'unknown',
                    'ip' => $request->ip()
                ]
            );
            return response()->view('errors.500', [], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в Controller@edit: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'subj_id' => $subj ?? 'unknown',
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]
            );
            return response()->view('errors.500', [], 500);
        }
    }

    /**
     * Метод по изменению позиций фото в фотоальбоме(контроллер)
     * @param Request $request
     * @return JsonResponse
     *
     */
    public function imgOrderChange(Request $request): JsonResponse
    {
        try {
            $data = $request->input('order');

            // Валидация входных данных
            if (!$data || !is_array($data)) {
                Log::channel('error_file')->error(
                    'Некорректные данные порядка изображений в Controller@imgOrderChange',
                    [
                        'request_data' => $request->all(),
                        'ip' => $request->ip()
                    ]
                );
                return response()->json([
                    'message' => 'Некорректные данные для изменения порядка',
                    'alert-type' => 'error'
                ], 400);
            }

            // Вызов сервиса для обработки
            $this->imgSubjService->updateImageOrder($data);

            return response()->json([
                'message' => 'Порядок изменён.',
                'alert-type' => 'success'
            ]);

        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в Controller@imgOrderChange: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'order_data' => $data ?? 'unknown',
                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'message' => 'Ошибка при сохранении порядка изображений',
                'alert-type' => 'error'
            ], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в Controller@imgOrderChange: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'message' => 'Произошла непредвиденная ошибка',
                'alert-type' => 'error'
            ], 500);
        }
    }

    /**
     * Метод по добавлению фото в VK
     * @param Request $request
     * @return JsonResponse
     */
    public function imgSubjStore(Request $request): JsonResponse
    {
        $id = $request->id;
        $address = AddressSubj::where('subj_id', $id)->first();

        try {
            if (!$request->hasFile('img')) {
                return response()->json([
                    'path' => null,
                    'id' => null,
                    'message' => 'Файл изображения не предоставлен'
                ], 400);
            }

            $album = AlbumsVk::where('city_id', $address->city_id)->first();
            $data = $this->imgSubjService->ImgSubjStore($request, $request->id, $album->group_id, $album->album_id);
            $res = [
                'path' => $data[0],
                'id' => $data[1],
                'message' => 'Изображение успешно загружено'
            ];

            return response()->json($res, 201);
        } catch (ValidationException $e) {
            Log::channel('error_file')->error(
                'Ошибка валидации в Controller@imgSubjStore: ' . $e->getMessage(),
                [
                    'input_data' => $request->except(['img']),
                    'file_name' => $request->file('img')?->getClientOriginalName(),
                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'path' => null,
                'id' => null,
                'message' => 'Ошибка валидации данных'
            ], 422);
        } catch (VkApiException $e) {
            Log::channel('error_file')->error(
                'Ошибка API ВКонтакте в Controller@imgSubjStore: ' . $e->getMessage(),
                [
                    'subj_id' => $request->id,
                    'vk_error_code' => $e->getErrorCode(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'path' => null,
                'id' => null,
                'message' => 'Ошибка при работе с API ВКонтакте'
            ], 500);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в Controller@imgSubjStore: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'subj_id' => $request->id,
                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'path' => null,
                'id' => null,
                'message' => 'Ошибка при сохранении данных в базу'
            ], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в Controller@imgSubjStore: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'subj_id' => $request->id ?? 'unknown',
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]
            );
            return response()->json([
                'path' => null,
                'id' => null,
                'message' => 'Произошла непредвиденная ошибка'
            ], 500);
        }
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        try {
            // Валидация ID: должно быть числом ≥ 1
            if (!is_numeric($id) || $id < 1 || intval($id) != $id) {
                Log::channel('error_file')->error(
                    'Некорректный формат ID в Controller@destroy',
                    [
                        'received_id' => $id,
                        'ip' => request()->ip(),
                        'user_agent' => request()->header('User-Agent')
                    ]
                );
                return response()->json(['error' => 'Invalid ID format'], 400);
            }

            $id = (int)$id; // Приводим к целому числу

            $result = $this->imgSubjService->deleteImgSubj($id);

            if (!$result) {
                Log::channel('error_file')->error(
                    'Попытка удаления несуществующего изображения в Controller@destroy',
                    [
                        'img_subj_id' => $id,
                        'ip' => request()->ip()
                    ]
                );
                return response()->json(['error' => 'Image not found'], 404);
            }

            return response()->json(['answer' => 'ok'], 200);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в Controller@destroy: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'img_subj_id' => $id ?? 'unknown',
                    'ip' => request()->ip()
                ]
            );
            return response()->json(['error' => 'Database error occurred'], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в Controller@destroy: ' . $e->getMessage(),
                [
                    'img_subj_id' => $id ?? 'unknown',
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => request()->ip()
                ]
            );
            return response()->json(['error' => 'Unexpected error occurred'], 500);
        }
    }


}
