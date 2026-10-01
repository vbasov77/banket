<?php

namespace App\Http\Controllers;

use App\Models\Obj;
use App\Services\ActionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ActionController extends Controller
{
    public function __construct(
        private readonly ActionService $actionService
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $objId = (int)$request->input('obj');

        try {
            $obj = Obj::findOrFail($objId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error('ActionController@create: объект не найден', [
                'obj_id' => $objId,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('unauthorized')->with([
                'error' => 'Объект не найден или у вас нет доступа.',
            ]);
        }

        if (!$obj->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для добавления акции',
            ]);
        }

        $action = $this->actionService->getActionByObjId($objId);

        if ($action) {
            return redirect()->route('action.edit', ['obj' => $objId]);
        }

        return view('actions.create', [
            'obj' => $obj,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $objId = (int)$request->input('obj');

        try {
            $obj = Obj::findOrFail($objId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error('ActionController@store: объект не найден', [
                'obj_id' => $objId,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('unauthorized')->with([
                'error' => 'Объект не найден.',
            ]);
        }

        if (!$obj->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для добавления акции',
            ]);
        }

        // Если ты хочешь хранить JSON-массив — валидируй иначе (см. примечание ниже)
        $validated = $request->validate([
            'actions' => 'required|string|max:5000',
        ]);

        try {
            $this->actionService->createAction($objId, $validated['actions']);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('ActionController@store: ошибка создания акции', [
                'obj_id' => $objId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Не удалось добавить акцию. Проверьте логи.');
        }

        return redirect()
            ->route('show.obj', ['id' => $objId])
            ->with('success', 'Акция добавлена.');
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $objId = (int)$request->input('obj');

        try {
            $obj = Obj::findOrFail($objId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error('ActionController@edit: объект не найден', [
                'obj_id' => $objId,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('unauthorized')->with([
                'error' => 'Объект не найден.',
            ]);
        }

        if (!$obj->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для редактирования этого субъекта',
            ]);
        }

        $action = $this->actionService->getActionByObjId($objId);

        if (!$action) {
            return redirect()->route('action.create', ['obj' => $objId])
                ->with('error', 'Акция не найдена. Создайте новую.');
        }

        return view('actions.edit', [
            'obj'    => $obj,
            'action' => $action,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $objId = (int)$request->input('obj');

        try {
            $obj = Obj::findOrFail($objId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error('ActionController@update: объект не найден', [
                'obj_id' => $objId,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('unauthorized')->with([
                'error' => 'Объект не найден.',
            ]);
        }

        if (!$obj->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для редактирования акции',
            ]);
        }

        $validated = $request->validate([
            'actions' => 'required|string|max:5000',
        ]);

        try {
            // Унифицированный порядок аргументов: сначала objId, потом данные
            $this->actionService->updateAction($validated['actions'], $objId);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('ActionController@update: ошибка обновления акции', [
                'obj_id' => $objId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Не удалось обновить акцию. Проверьте логи.');
        }

        return redirect()
            ->route('show.obj', ['id' => $objId])
            ->with('success', 'Акция обновлена.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $objId = (int)$request->input('obj');

        try {
            $obj = Obj::findOrFail($objId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::channel('error_file')->error('ActionController@destroy: объект не найден', [
                'obj_id' => $objId,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('unauthorized')->with([
                'error' => 'Объект не найден.',
            ]);
        }

        if (!$obj->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для удаления акции',
            ]);
        }

        $action = $this->actionService->getActionByObjId($objId);

        if ($action) {
            try {
                // Удаляем по objId (или по ID акции, зависит от реализации сервиса)
                $this->actionService->deleteAction($action);
            } catch (\Exception $e) {
                Log::channel('error_file')->error('ActionController@destroy: ошибка удаления акции', [
                    'obj_id' => $objId,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'Не удалось удалить акцию. Проверьте логи.');
            }
        }

        return redirect()
            ->route('show.obj', ['id' => $objId])
            ->with('success', 'Акция удалена.');
    }
}
