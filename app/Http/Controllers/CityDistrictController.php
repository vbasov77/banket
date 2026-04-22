<?php

namespace App\Http\Controllers;

use App\Services\CityDistrictService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CityDistrictController extends Controller
{
    protected CityDistrictService $cityDistrictService;

    public function __construct(CityDistrictService $cityDistrictService)
    {
        $this->cityDistrictService = $cityDistrictService;
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view('city-district.create');
    }

    /**
     * Добавление города и районов
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'city_name' => 'required|string|max:255',
            'districts' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::channel('error_file')->warning('Validation failed in CityDistrictController@store', [
                'errors' => $validator->errors()->all(),
                'input' => $request->except(['_token']),
            ]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Вызов сервиса
        $result = $this->cityDistrictService->storeCityAndDistricts($request->all());

        // Обработка результата
        if ($result['success']) {
            return redirect()->route('city-district.create')
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }
}
