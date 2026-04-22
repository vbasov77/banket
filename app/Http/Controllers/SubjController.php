<?php

namespace App\Http\Controllers;

use App\Http\Requests\Obj\EditObjRequest;
use App\Http\Requests\Subj\CreateSubjRequest;
use App\Http\Requests\Subj\EditSubjRequest;
use App\Models\City;
use App\Models\ImgObj;
use App\Models\ImgSubj;
use App\Models\Obj;
use App\Models\Subj;
use App\Models\UserCity;
use App\Services\DetailsObjService;
use App\Services\ImgObjService;
use App\Services\ImgSubjService;
use App\Services\ObjService;
use App\Services\SubjService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PHPUnit\Exception;

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
        } catch (\Illuminate\Validation\ValidationException $e) {
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
     * @throws AuthenticationException
     */
    public function myObj(Request $request): View
    {

        $objId = $this->objService->findIdObjByUserId(Auth::user()->id);
        $data = null;
        $error = null;
        if(!empty($request->error)){
            $error = $request->error;
        }

        if ($objId) {
            $data = $this->subjService->findMySubjs($objId);
        }

        return \view('objects.subjects.my_subjs', ['data' => $data, 'error' => $error]);
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $subj = $this->subjService->findByIdForEdit($id);
        $images = $this->subjService->existsImg($id);

        return \view('objects.subjects.edit', ['subj' => $subj, 'images' => $images]);
    }


    public function update(EditSubjRequest $request)
    {
        try {
            $this->subjService->update($request->validated(), (int)$request->input('subj_id'));

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

    public function takeOff(Request $request)
    {
        try {
            Subj::where('id', $request->id)->update(['published' => 0]);
            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация снята'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'answer' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function published(Request $request)
    {
        try {
            Subj::where('id', $request->id)->update(['published' => 1]);
            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация снята'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'answer' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
