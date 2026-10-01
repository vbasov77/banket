<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\ObjFeatures\CreateObjFeaturesRequest;
use App\Http\Requests\ObjFeatures\EditObjFeaturesRequest;
use App\Models\Obj;
use App\Models\ObjFeature;
use App\Services\ObjFeatureService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ObjFeaturesController extends Controller
{

    private ObjFeatureService $featureService;

    public function __construct(ObjFeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function createObjFeatures(Request $request): View|RedirectResponse
    {
        try {
            $obj = Obj::findOrFail($request->id);
            return view('obj_features.create', compact('obj'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error('Объект не найден при создании особенностей', [
                'obj_id' => $request->id ?? null,
                'exception' => $e->getMessage(),
            ]);
            return redirect()->route('my.obj')
                ->with('error', 'Объект не найден. Возможно, он был удалён.');

        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Ошибка при открытии формы создания особенностей', [
                'obj_id' => $request->id ?? null,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('my.obj')
                ->with('error', 'Произошла ошибка. Попробуйте позже.');
        }
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function editObjFeatures(Request $request): View|RedirectResponse
    {
        try {
            $feature = ObjFeature::where('id', $request->id)->firstOrFail();
            $obj = $feature->obj;

            if (!$obj) {
                Log::channel('error_file')->error('Объект не найден для особенностей', [
                    'feature_id' => $feature->id,
                    'obj_id' => $feature->obj_id,
                ]);
                return redirect()->route('my.obj')
                    ->with('error', 'Объект не найден.');
            }

            return view('obj_features.edit', compact('feature', 'obj'));

        } catch (ModelNotFoundException $e) {
            Log::channel('error_file')->error('Особенности объекта не найдены при редактировании', [
                'id' => $request->id ?? null,
                'exception' => $e->getMessage(),
            ]);
            // Если записи нет — перекидываем на форму создания
            // Но тут нам нужен obj_id, а не id записи
            return redirect()->route('my.obj')
                ->with('error', 'Особенности ещё не заполнены.');

        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Ошибка при открытии формы редактирования особенностей', [
                'id' => $request->id ?? null,
                'exception' => $e->getMessage(),
            ]);
            return redirect()->route('my.obj')
                ->with('error', 'Произошла ошибка. Попробуйте позже.');
        }
    }


    /**
     * @param CreateObjFeaturesRequest $request
     * @return RedirectResponse
     * @throws \Illuminate\Database\QueryException
     */
    public function storeObjFeatures(CreateObjFeaturesRequest $request): RedirectResponse
    {
        try {
            // UX-логика: если особенности уже есть — не создаём, а редиректим на редактирование
            if ($this->featureService->existsForObj($request->obj_id)) {
                Log::channel('error_file')->info('Попытка создать дубликат особенностей', [
                    'obj_id' => $request->obj_id,
                ]);
                return redirect()
                    ->route('edit.obj_features', ['id' => $request->obj_id])
                    ->with('error', 'Особенности для этого объекта уже существуют. Открываю редактирование.');
            }

            $this->featureService->upsert($request->validated());

            return redirect()->route('my.obj')
                ->with('message', 'Особенности объекта сохранены.');

        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error($e->getMessage(), [
                'obj_id' => $request->obj_id ?? null,
            ]);
            return back()->with('error', $e->getMessage())->withInput();
        } catch (QueryException $e) {
            Log::channel('error_file')->error('Ошибка БД при сохранении особенностей', [
                'obj_id'    => $request->obj_id ?? null,
                'exception' => $e->getMessage(),
                'sql'       => $e->getSql(),
            ]);
            return back()
                ->with('error', 'Ошибка базы данных при сохранении. Попробуйте позже.')
                ->withInput();
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Непредвиденная ошибка при сохранении особенностей', [
                'obj_id'    => $request->obj_id ?? null,
                'exception' => $e->getMessage(),
            ]);
            return back()
                ->with('error', 'Произошла ошибка при сохранении. Попробуйте позже.')
                ->withInput();
        }
    }

    /**
     * @param EditObjFeaturesRequest $request
     * @param $id
     * @return RedirectResponse
     * @throws \Illuminate\Database\QueryException
     */
    public function updateObjFeatures(EditObjFeaturesRequest $request, int $id): RedirectResponse
    {
        try {
            $objId = $request->input('obj_id');

            $exists = $this->featureService->existsForObj($objId);
            if (!$exists) {
                Log::channel('error_file')->info('Запись особенностей не найдена при обновлении — будет создана новая', [
                    'obj_id' => $objId,
                ]);
            }

            $data = $request->validated();
            $data['obj_id'] = $objId;

            $this->featureService->upsert($data);

            return redirect()->route('my.obj')
                ->with('message', $exists
                    ? 'Особенности объекта обновлены.'
                    : 'Особенности объекта созданы (ранее не существовали).');

        } catch (\RuntimeException $e) {
            Log::channel('error_file')->error($e->getMessage(), ['id' => $id]);
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error('Ошибка БД при обновлении особенностей', [
                'id' => $id, 'exception' => $e->getMessage(),
            ]);
            return back()->with('error', 'Ошибка базы данных при обновлении. Попробуйте позже.')->withInput();
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Непредвиденная ошибка при обновлении особенностей', [
                'id' => $id, 'exception' => $e->getMessage(),
            ]);
            return back()->with('error', 'Произошла ошибка при обновлении. Попробуйте позже.')->withInput();
        }
    }

}
