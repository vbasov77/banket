<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityDistrictController extends Controller
{
    public function create()
    {
        return view('city-district.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_name' => 'required|string|max:255',
            'districts' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $cityName = trim($request->city_name);

        // Проверка существования города
        $existingCity = City::where('name', $cityName)->first();

        if ($existingCity) {
            // Город уже существует — используем его
            $city = $existingCity;
            $message = 'Районы успешно добавлены к существующему городу!';
        } else {
            // Города нет — создаём новый
            $city = City::create([
                'name' => $cityName,
            ]);
            $message = 'Город и районы успешно добавлены!';
        }

        // Обрабатываем многострочный ввод районов с ";" в конце
        $districtLines = preg_split('/\r\n|\r|\n/', $request->districts);
        $districtNames = [];

        foreach ($districtLines as $line) {
            $cleanLine = trim(rtrim($line, ';'));
            if (!empty($cleanLine)) {
                $districtNames[] = $cleanLine;
            }
        }

        // Сохраняем районы (проверяем, нет ли уже таких у этого города)
        foreach ($districtNames as $districtName) {
            // Проверяем существование района для данного города
            $existingDistrict = District::where('city_id', $city->id)
                ->where('name', $districtName)
                ->first();

            if (!$existingDistrict) {
                // Района нет — добавляем
                $city->districts()->create([
                    'name' => $districtName,
                ]);
            }
            // Если район уже есть — пропускаем его
        }

        return redirect()->route('city-district.create')
            ->with('success', $message);
    }}
