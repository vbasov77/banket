<?php

namespace App\Http\Controllers;

use App\Http\Requests\Obj\CreateObjRequest;
use App\Http\Requests\Obj\EditObjRequest;
use App\Models\Obj;
use App\Services\ImgObjService;
use App\Services\ObjService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PHPUnit\Exception;

class ObjController extends Controller
{
    private ObjService $objService;
    private ImgObjService $imgService;


    public function __construct(ObjService $objService, ImgObjService $imgService)
    {
        $this->objService = $objService;
        $this->imgService = $imgService;
    }


    /**
     * @return View
     */
    public function create(): View
    {
        $obj = Obj::where('user_id', Auth::id())->first();
        if ($obj) {
            return view('objects.error', ['id' => $obj->id]);
        }

        try {
            $userId = Auth::id();

            // Проверка авторизации пользователя
            if ($userId === null) {
                Log::channel('error_file')->error(
                    'Попытка доступа к objects.create без авторизации'
                );
                abort(403, 'Доступ запрещён: требуется авторизация');
            }

            return view('objects.create', ['user' => $userId]);
        } catch (\Illuminate\View\ViewException $e) {
            // Ошибки рендеринга шаблона (не найден шаблон, синтаксическая ошибка и т. д.)
            Log::channel('error_file')->error(
                'Ошибка рендеринга шаблона objects.create: ' . $e->getMessage(),
                [
                    'user_id' => $userId ?? 'unknown',
                    'exception_code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            );
            abort(500, 'Ошибка загрузки страницы — шаблон недоступен');
        } catch (\Exception $e) {
            // Общий обработчик для любых других непредвиденных ошибок
            Log::channel('error_file')->error(
                'Неожиданная ошибка в DetailsObjController@create: ' . $e->getMessage(),
                [
                    'user_id' => $userId ?? 'unknown',
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            abort(500, 'Внутренняя ошибка сервера');
        }
    }


    /**
     * @param CreateObjRequest $request
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function store(CreateObjRequest $request): RedirectResponse|View
    {
        $obj = Obj::where('user_id', Auth::id())->first();
        if ($obj) {
            return view('objects.error', ['id' => $obj->id]);
        }

        try {
            $obj = $this->objService->store($request->validated());
            return redirect()->route("create.details_obj", ['obj' => $obj]);
        } catch (ValidationException $e) {
            Log::channel('error_file')->error(
                'Ошибка валидации в ObjController@store: ' . $e->getMessage(),
                [
                    'errors' => $e->errors(),
                    'user_id' => auth()->id()
                ]
            );
            throw $e;
        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error(
                'Бизнес‑ошибка при создании объекта: ' . $e->getMessage(),
                [
                    'input_data' => $request->validated(),
                    'user_id' => auth()->id(),
                    'exception_code' => $e->getCode()
                ]
            );

            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в ObjController@store: ' . $e->getMessage(),
                [
                    'input_data' => $request->validated(),
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );

            return redirect()
                ->back()
                ->withErrors(['error' => 'Произошла внутренняя ошибка сервера'])
                ->withInput();
        }
    }


    public function show(Request $request): View
    {
        try {
            $objId = $request->id;

            if (!$objId) {
                Log::channel('error_file')->error(
                    'Missing object ID in MapController@show'
                );
                abort(400, 'Object ID is required');
            }

            $obj = $this->objService->findById($objId);

            // Проверяем, найден ли объект
            if (!$obj) {
                Log::channel('error_file')->error(
                    'Object not found for ID: ' . $objId
                );
                abort(404, 'Object not found');
            }

            $images = $this->imgService->findImgByObjId($objId);

            // Обрабатываем случай, когда изображений нет (это не ошибка)
            if ($images === null) {
                $images = collect(); // Возвращаем пустую коллекцию вместо null
            }

            return view('objects.show', ['obj' => $obj, 'images' => $images]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in MapController@show: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $request->id ?? 'unknown']
            );
            abort(500, 'Internal server error');
        }
    }


    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        try {
            $objId = $request->id;

            if (!$objId) {
                Log::channel('error_file')->error(
                    'Missing object ID in ObjectsController@edit'
                );
                abort(400, 'Object ID is required');
            }

            $obj = $this->objService->findByIdOnlyObj($objId);

            // Проверяем, найден ли объект
            if (!$obj) {
                Log::channel('error_file')->error(
                    'Object not found for ID: ' . $objId
                );
                abort(404, 'Object not found');
            }

            return view('objects.edit', ['obj' => $obj]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in ObjectsController@edit: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $objId ?? 'unknown']
            );
            abort(500, 'Internal server error');
        }
    }


    /**
     * @param EditObjRequest $request
     * @return RedirectResponse
     */
    public function update(EditObjRequest $request): RedirectResponse
    {
        try {
            $objId = $request->id;

            if (!$objId) {
                Log::channel('error_file')->error(
                    'Missing object ID in ObjectsController@update'
                );
                return redirect()->route('my.obj')->with('error', 'Object ID is required');
            }

            $this->objService->update($request->validated(), $objId);

            return redirect()->route('my.obj')->with('success', 'Object updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'Database query error in ObjectsController@update: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $request->id ?? 'unknown', 'sql' => $e->getSql()]
            );
            return redirect()->route('my.obj')->with('error', 'Database error occurred while updating object');
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in ObjectsController@update: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'obj_id' => $request->id ?? 'unknown']
            );
            return redirect()->route('my.obj')->with('error', 'An error occurred while updating object');
        }
    }

    /**
     * @throws AuthenticationException
     */
    public function myObj(Request $request): View
    {
        try {
            $userId = Auth::user()->id;

            // Получаем ID объекта пользователя
            $objId = $this->objService->findIdObjByUserId();

            $data = null;
            $error = null;

            if (!empty($request->error)) {
                $error = $request->error;
            }

            // ИСПРАВЛЕНИЕ: берём сообщение из сессии, а не из запроса
            $message = session('message');

            if ($objId) {
                // Получаем данные по субъектам
                $data = $this->objService->findMySubjs($objId);

                if ($data === null) {
                    Log::channel('error_file')->error(
                        'Failed to get subjects data for obj_id: ' . $objId,
                        ['user_id' => $userId]
                    );
                    $error = 'Не удалось загрузить данные по субъектам';
                }
            } else {
                Log::channel('error_file')->error(
                    'User has no associated object',
                    ['user_id' => $userId]
                );
            }

            return view('objects.subjects.my_subjs', [
                'data' => $data,
                'message' => $message,
                'error' => $error,
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in Controller@myObj: ' . $e->getMessage(),
                [
                    'trace' => $e->getTrace(),
                    'user_id' => Auth::user()->id ?? 'guest',
                ]
            );

            return view('objects.subjects.my_subjs', [
                'data' => null,
                'error' => 'Произошла критическая ошибка при загрузке данных',
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
