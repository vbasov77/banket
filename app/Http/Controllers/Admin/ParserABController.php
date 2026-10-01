<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ParserABService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;

class ParserABController extends Controller
{
    public function __construct(
        private ParserABService $parserService,
    )
    {
    }

    /**
     * Показ формы ввода ссылки.
     */
    public function form()
    {
        return view('parsers.ab.parser');
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function parse(Request $request): View|RedirectResponse
    {
        $request->validate([
            'url' => 'required|url',
            'url_gorko' => 'nullable|url',
        ]);

        try {
            $data = $this->parserService->processData(
                $request->input('url'),
                $request->input('url_gorko'),
            );
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['url' => 'Не удалось загрузить страницу: ' . $e->getMessage()]);
        }

        return view('parsers.ab.parser', [
            'url' => $request->input('url'),
            'url_gorko' => $request->input('url_gorko'),
            'result' => $data,
        ]);
    }

    /**
     * Сохранение распарсенных данных в БД.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->except(['_token', 'source_url']);

        // Селекты: берём метки, где статус не пустой и не «отсутствует»
        $equipment = [];
        foreach ($request->input('equipment_sel', []) as $label => $status) {
            if ($status !== '' && $status !== 'отсутствует') {
                $equipment[] = $label;
            }
        }
        // Чекбоксы
        $equipment = array_values(array_unique(array_merge(
            $equipment,
            $request->input('equipment_chk', [])
        )));
        $kids = array_values(array_unique($request->input('kids_chk', [])));

        $data['equipment'] = $equipment;
        $data['kids'] = $kids;

        unset($data['equipment_sel'], $data['equipment_chk'], $data['kids_chk']);

        try {
            $id = $this->parserService->storeParsedData($data, Auth::id());
            Log::channel('info_file')->info([$id]);

            return redirect()->route('admin.show_obj', ['id' => $id]);

        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Ошибка сохранения объекта', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'name_obj' => $data['name_obj'] ?? null,
                'user_id' => Auth::id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ошибка сохранения: ' . $e->getMessage());
        }
    }
}
