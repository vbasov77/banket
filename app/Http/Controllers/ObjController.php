<?php

namespace App\Http\Controllers;

use App\Http\Requests\Obj\CreateObjRequest;
use App\Http\Requests\Obj\EditObjRequest;
use App\Models\Obj;
use App\Services\ImgObjService;
use App\Services\ObjService;
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
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(CreateObjRequest $request): RedirectResponse
    {
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
        $obj = $this->objService->findById($request->id);
        $images = $this->imgService->findImgByObjId($request->id);

        return \view('objects.show', ['obj' => $obj, 'images' => $images]);
    }


    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        $obj = $this->objService->findByIdOnlyObj($request->id);

        return \view('objects.edit', ['obj' => $obj]);
    }


    /**
     * @param EditObjRequest $request
     * @return RedirectResponse|string
     */
    public function update(EditObjRequest $request)
    {
        try {
            $this->objService->update($request->validated(), $request->id);

            return redirect()->route('my.obj');
        } catch (Exception $e) {
            return "No";
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
