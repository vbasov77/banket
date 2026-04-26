<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subj\CreateSubjRequest;
use App\Http\Requests\Subj\EditSubjRequest;
use App\Models\Subj;
use App\Services\ObjService;
use App\Services\SubjService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubjController extends Controller
{
    private SubjService $subjService;
    private ObjService $objService;

    public function __construct(ObjService  $objService,
                                SubjService $subjService)
    {
        $this->subjService = $subjService;
        $this->objService = $objService;
    }

    /**
     * @return View
     */
    public function create(): View
    {
        try {
            $objId = $this->subjService->findIdObjByUserId();

            return view('objects.subjects.create', ['objId' => $objId]);
        } catch (AuthenticationException $e) {
            Log::channel('error_file')->error(
                'Попытка доступа к objects.subjects.create без авторизации'
            );
            abort(403, 'Доступ запрещён: требуется авторизация');
        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error(
                'Бизнес‑ошибка в ObjController@create: ' . $e->getMessage(),
                [
                    'user_id' => auth()->id(),
                    'exception_code' => $e->getCode()
                ]
            );
            return view('objects.subjects.create', ['objId' => null])
                ->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в ObjController@create: ' . $e->getMessage(),
                [
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            return view('objects.subjects.create', ['objId' => null])
                ->withErrors(['error' => 'Произошла внутренняя ошибка сервера']);
        }
    }


    /**
     * @param CreateSubjRequest $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(CreateSubjRequest $request): RedirectResponse
    {
        try {
            $nameSubj = $request->input('name_subj');
            $objId = $request->input('obj_id');

            $result = $this->subjService->createSubj($nameSubj, $objId, $request->validated());

            if ($result['exists']) {
                return redirect()->route('my.obj')->with([
                    'error' => "Субъект с названием '" . $nameSubj . "' уже существует."
                ]);
            }

            if ($result['success']) {
                return redirect()->route('edit.img_subj', ['id' => $result['subj']->id]);
            }

            return redirect()->route('my.obj')->with([
                'error' => 'Не удалось создать субъект'
            ]);
        } catch (ValidationException $e) {
            Log::channel('error_file')->error(
                'Ошибка валидации в SubjController@store: ' . $e->getMessage(),
                [
                    'errors' => $e->errors(),
                    'user_id' => auth()->id()
                ]
            );
            throw $e;
        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error(
                'Бизнес‑ошибка в SubjController@store: ' . $e->getMessage(),
                [
                    'input_data' => $request->validated(),
                    'user_id' => auth()->id(),
                    'exception_code' => $e->getCode()
                ]
            );
            return redirect()->route('my.obj')->with([
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в SubjController@store: ' . $e->getMessage(),
                [
                    'input_data' => $request->validated(),
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );
            return redirect()->route('my.obj')->with([
                'error' => 'Произошла внутренняя ошибка сервера'
            ]);
        }
    }


    /**
     * @param Request $request
     * @return View
     */
    public function show(Request $request): View
    {
        try {
            $id = $request->id;
            $subj = $this->subjService->findById($id);
            $nearestObjects = null;
            if (!empty($subj['longitude'])) {
                $nearestObjects = $this->subjService->findNearestObjects(
                    $subj['latitude'],
                    $subj['longitude'],
                    $subj['obj']['obj_id']
                );
            }

            return view('objects.subjects.show', [
                'subj' => $subj,
                'nearestObjects' => $nearestObjects
            ]);
        } catch (\InvalidArgumentException $e) {
            Log::channel('error_file')->error(
                'Ошибка координат в SubjController@show: ' . $e->getMessage(),
                [
                    'latitude' => $subj['latitude'] ?? null,
                    'longitude' => $subj['longitude'] ?? null,
                    'obj_id' => $subj['obj']['obj_id'] ?? null,
                    'user_id' => auth()->id()
                ]
            );

            return view('objects.subjects.show', [
                'subj' => $subj,
                'nearestObjects' => null,
                'error' => 'Некорректные координаты объекта'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в SubjController@show: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'input_data' => $request->all(),
                    'user_id' => auth()->id()
                ]
            );

            return view('objects.subjects.show', [
                'subj' => $subj,
                'nearestObjects' => null,
                'error' => 'Ошибка при получении ближайших объектов'
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в SubjController@show: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'user_id' => auth()->id(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            );

            return view('objects.subjects.show', [
                'subj' => $subj,
                'nearestObjects' => null,
                'error' => 'Произошла внутренняя ошибка сервера'
            ]);
        }
    }


    /**
     * Редактирование субъекта
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function edit(Request $request): Application|Factory|View|RedirectResponse
    {
        try {
            $id = $request->id;

            // Получаем субъект для проверки прав
            $subj = Subj::find($id);


            if (!$subj) {
                Log::channel('error_file')->warning('Attempt to edit non-existent subj', [
                    'subj_id' => $id,
                    'user_id' => auth()->id()
                ]);

                return back()->withErrors(['error' => 'Указанный субъект не найден']);
            }

            // Проверка прав доступа через существующий Gate 'can-access'
            if (!Gate::allows('can-access', $subj)) {
                Log::channel('error_file')->error('Unauthorized subj edit attempt', [
                    'subj_id' => $id,
                    'user_id' => auth()->id(),
                    'model_user_id' => $subj->user_id ?? 'null',
                    'related_obj_user_id' => $subj->obj->user_id ?? 'null'
                ]);

                return back()->withErrors(['error' => 'У вас нет прав для редактирования этого субъекта']);
            }

            // Если права есть — загружаем данные для формы редактирования
//            $subjData = $this->subjService->findByIdForEdit($id);
            $images = $this->subjService->existsImg($id);

            return view('objects.subjects.edit', [
                'subj' => $subj,
                'images' => $images
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error in Subj edit', [
                'exception' => $e->getMessage(),
                'subj_id' => $request->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Произошла непредвиденная ошибка при загрузке формы редактирования']);
        }
    }


    /**
     * @param EditSubjRequest $request
     * @return RedirectResponse
     */
    public function update(EditSubjRequest $request): RedirectResponse
    {
        try {
            $subjId = (int)$request->input('subj_id');

            // Получаем субъект для проверки прав
            $subj = Subj::find($subjId);

            if (!$subj) {
                Log::channel('error_file')->warning('Attempt to update non-existent subj', [
                    'subj_id' => $subjId,
                    'user_id' => auth()->id(),
                    'input_data' => $request->validated()
                ]);

                return back()->withErrors(['error' => 'Указанный субъект не найден'])->withInput();
            }

            // Проверка прав доступа через существующий Gate 'can-access'
            if (!Gate::allows('can-access', $subj)) {
                Log::channel('error_file')->warning('Unauthorized subj update attempt', [
                    'subj_id' => $subjId,
                    'user_id' => auth()->id(),
                    'attempted_data' => $request->validated(),
                    'model_user_id' => $subj->user_id ?? 'null',
                    'related_obj_user_id' => $subj->obj->user_id ?? 'null'
                ]);

                return back()->withErrors(['error' => 'У вас нет прав для редактирования этого субъекта'])->withInput();
            }

            // Если права есть — выполняем обновление
            $this->subjService->update($request->validated(), $subjId);
            return redirect()->route('my.obj');

        } catch (QueryException $e) {
            Log::channel('error_file')->error('Database query error in Subj update', [
                'exception' => $e->getMessage(),
                'subj_id' => $request->input('subj_id'),
                'user_id' => auth()->id(),
                'input_data' => $request->validated()
            ]);
            return back()->withErrors(['error' => 'Ошибка базы данных при обновлении'])->withInput();
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error in Subj update', [
                'exception' => $e->getMessage(),
                'subj_id' => $request->input('subj_id'),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Произошла непредвиденная ошибка'])->withInput();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function takeOff(Request $request): JsonResponse
    {
        try {
            $subj = Subj::with('obj')->findOrFail($request->id);

            if (!Gate::allows('can-access', $subj)) {
                Log::channel('error_file')->error('Unauthorized takeOff attempt for Subj', [
                    'user_id' => auth()->id(),
                    'subj_id' => $subj->id,
                    'obj_owner_id' => $subj->obj?->user_id
                ]);
                return response()->json([
                    'answer' => 'error',
                    'message' => 'У вас нет прав для выполнения этого действия'
                ], 403);
            }

            $subj->update(['published' => 0]);

            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация снята'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException  $e) {
            return response()->json([
                'answer' => 'error',
                'message' => 'Объект не найден'
            ], 404);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in takeOff', [
                'exception' => $e->getMessage(),
                'subj_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'answer' => 'error',
                'message' => 'Произошла ошибка при снятии публикации'
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function published(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'answer' => 'error',
                    'message' => 'Неавторизованный доступ'
                ], 401);
            }


            $subj = Subj::with('obj')->findOrFail($request->id);

            // Проверка прав: админ ИЛИ владелец связанного Obj
            if (!Gate::allows('can-access', $subj)) {
                Log::channel('error_file')->error('Unauthorized publish attempt', [
                    'user_id' => $user->id,
                    'subj_id' => $subj->id,
                    'obj_owner_id' => $subj->obj?->user_id
                ]);
                return response()->json([
                    'answer' => 'error',
                    'message' => 'У вас нет прав для выполнения этого действия'
                ], 403);
            }

            $subj->update(['published' => 1]);

            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация опубликована' // Исправлено сообщение
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'answer' => 'error',
                'message' => 'Объект не найден'
            ], 404);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in publish', [
                'exception' => $e->getMessage(),
                'subj_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'answer' => 'error',
                'message' => 'Произошла ошибка при публикации'
            ], 500);
        }
    }


}
