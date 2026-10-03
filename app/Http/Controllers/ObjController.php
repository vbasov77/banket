<?php

namespace App\Http\Controllers;

use App\Http\Requests\Obj\CreateObjRequest;
use App\Http\Requests\Obj\EditObjRequest;
use App\Models\Obj;
use App\Services\ImgBanSubjService;
use App\Services\ImgObjService;
use App\Services\MailService;
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
    private ImgBanSubjService $imgBanSubjService;


    public function __construct(ObjService $objService, ImgBanSubjService $imgBanSubjService)
    {
        $this->objService = $objService;
        $this->imgBanSubjService = $imgBanSubjService;
    }


    /**
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        $userId = Auth::id();

        try {
            // Проверка авторизации пользователя
            if ($userId === null) {
                Log::channel('error_file')->error(
                    'Попытка доступа к objects.create без авторизации'
                );
                abort(403, 'Доступ запрещён: требуется авторизация');
            }

            // Проверка лимита — если уже достигнут, не даём открыть форму
            $user = Auth::user();
            $currentCount = Obj::where('user_id', $userId)->count();

            if ($currentCount >= $user->getMaxObjects()) {
                Log::channel('info_file')->info('Попытка открыть форму создания при достигнутом лимите', [
                    'user_id' => $userId,
                    'current_count' => $currentCount,
                ]);

                return redirect()
                    ->route('my.obj')
                    ->withErrors(['error' => 'Превышен лимит объектов']);
            }

            return view('objects.create', ['user' => $userId]);
        } catch (\Illuminate\View\ViewException $e) {
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
            Log::channel('error_file')->error(
                'Неожиданная ошибка в ObjController@create: ' . $e->getMessage(),
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
    public function store(CreateObjRequest $request, MailService $mailService): RedirectResponse|View
    {
        $user = Auth::user();

        // Проверка лимита
        $maxObjects = $user->getMaxObjects();
        $currentCount = Obj::where('user_id', $user->id)->count();

        if ($currentCount >= $maxObjects) {
            Log::channel('info_file')->info('Лимит объектов превышен', [
                'user_id' => $user->id,
                'current_count' => $currentCount,
                'limit' => $maxObjects,
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Превышен лимит объектов'])
                ->withInput();
        }

        try {
            $obj = $this->objService->store($request->validated());
            $mailService->sendAddNewObj();

            return redirect()->route('create.details_obj', ['id' => $obj->id]);
        } catch (ValidationException $e) {
            Log::channel('error_file')->error(
                'Ошибка валидации в ObjController@store: ' . $e->getMessage(),
                [
                    'errors' => $e->errors(),
                    'user_id' => $user->id,
                ]
            );
            throw $e;
        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error(
                'Бизнес-ошибка при создании объекта: ' . $e->getMessage(),
                [
                    'input_data' => $request->validated(),
                    'user_id' => $user->id,
                    'exception_code' => $e->getCode(),
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
                    'user_id' => $user->id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
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
            $metaDescription = $this->objService->findMetaDescription($obj);

            // Проверяем, найден ли объект
            if (!$obj) {
                Log::channel('error_file')->error(
                    'Object not found for ID: ' . $objId
                );
                abort(404, 'Object not found');
            }

            return view('objects.show', ['obj' => $obj, 'metaDescription' => $metaDescription]);
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
            $error = !empty($request->error) ? $request->error : null;
            $message = session('message');

            // Получаем все объекты пользователя
            $objIds = $this->objService->findIdsObjByUserId();

            // Нет объектов — показываем экран "создайте объект"
            if (empty($objIds)) {
                return view('objects.subjects.my_subjs', [
                    'data' => null,
                    'message' => $message,
                    'error' => $error,
                ]);
            }

            // Один объект — текущая логика без изменений
            if (count($objIds) === 1) {
                $objId = $objIds[0];
                $data = $this->objService->findMySubjs($objId);

                if ($data === null) {
                    Log::channel('error_file')->error(
                        'Failed to get subjects data for obj_id: ' . $objId,
                        ['user_id' => $userId]
                    );
                    $error = 'Не удалось загрузить данные по субъектам';
                }

                return view('objects.subjects.my_subjs', [
                    'data' => $data,
                    'message' => $message,
                    'error' => $error,
                ]);
            }

            // Несколько объектов — показываем список
            $objects = $this->objService->findObjsByIds($objIds);

            return view('objects.my_objects', [
                'objects' => $objects,
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

    public function viewObj(int $id): View
    {
        try {
            $obj = Obj::findOrFail($id);

            // Проверка прав
            if (!Auth::user()->isAuthor($obj)) {
                abort(403);
            }

            $data = $this->objService->findMySubjs($id);

            if ($data === null) {
                Log::channel('error_file')->error(
                    'Failed to get subjects data for obj_id: ' . $id,
                    ['user_id' => Auth::id()]
                );
            }

            return view('objects.subjects.my_subjs', [
                'data' => $data,
                'message' => session('message'),
                'error' => $data === null ? 'Не удалось загрузить данные по субъектам' : null,
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Error in Controller@viewObj: ' . $e->getMessage(),
                ['trace' => $e->getTrace(), 'user_id' => Auth::id()]
            );

            return view('objects.subjects.my_subjs', [
                'data' => null,
                'error' => 'Объект не найден или доступ запрещён',
            ]);
        }
    }

    public function deleteConfirm(int $id): View|RedirectResponse
    {
        $obj = Obj::findOrFail($id);

        if (!$obj->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для удаления этого объекта',
            ]);
        }

        $confirmText = "Удалить объект id{$id}";

        return view('objects.delete_confirm', [
            'obj' => $obj,
            'confirmText' => $confirmText,
        ]);
    }


    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $obj = Obj::findOrFail($id);

        if (!$obj->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для удаления этого объекта',
            ]);
        }

        // Проверка: текст из поля должен совпадать с "Удалить объект id{id}"
        $expectedText = "Удалить объект id{$id}";
        $confirmInput = $request->input('confirm_input', '');

        if (trim($confirmInput) !== $expectedText) {
            return redirect()->back()
                ->withErrors(['confirm_input' => 'Текст подтверждения не совпадает'])
                ->withInput();
        }

        // Доп. проверка: ID из скрытого поля должен совпадать с ID из URL
        $confirmId = (int) $request->input('confirm_id', 0);
        if ($confirmId !== (int) $id) {
            Log::channel('error_file')->warning(
                'ID mismatch on delete: url_id=' . $id . ', form_id=' . $confirmId,
                ['user_id' => Auth::id()]
            );
            return redirect()->back()
                ->withErrors(['confirm_input' => 'Ошибка проверки данных'])
                ->withInput();
        }

        try {
            $objName = $obj->name_obj;

            // Сначала чистим фото на хостинге
            $this->imgBanSubjService->destroyObjImages($obj->id);

            $obj->delete();

            Log::channel('error_file')->info("Объект '{$objName}' (id={$id}) удалён пользователем " . Auth::id());

            return redirect()->route('my.obj')->with([
                'message' => "Объект «{$objName}» удалён",
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка при удалении объекта: ' . $e->getMessage(),
                ['obj_id' => $id, 'user_id' => Auth::id()]
            );

            return redirect()->back()
                ->withErrors(['error' => 'Не удалось удалить объект'])
                ->withInput();
        }
    }

}
