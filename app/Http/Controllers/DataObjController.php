<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class DataObjController extends Controller
{

    /**
     * Форма создания нового ресторана.
     */
    public function create()
    {
        return view('data_obj.create');
    }

    /**
     * Сохранение нового ресторана.
     */
    public function store(Request $request)
    {
        $data = $this->prepareData($request);
        dd($data);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $restaurant = Restaurant::create(array_merge($validated, $data));

        // Сохраняем залы, если пришли
        if ($request->has('halls')) {
            $this->saveHalls($restaurant, $request->input('halls'));
        }

        return redirect()
            ->route('admin.restaurants.index')
            ->with('success', 'Ресторан создан.');
    }

    /**
     * Форма редактирования ресторана.
     */
    public function edit(Restaurant $restaurant)
    {
        return view('data_obj.edit', [
            'restaurant' => $restaurant,
        ]);
    }

    /**
     * Обновление ресторана.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $data = $this->prepareData($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $restaurant->update(array_merge($validated, $data));

        // Обновляем залы, если пришли
        if ($request->has('halls')) {
            $this->saveHalls($restaurant, $request->input('halls'));
        }

        return redirect()
            ->route('admin.restaurants.index')
            ->with('success', 'Ресторан обновлён.');
    }

    /**
     * Удаление ресторана.
     */
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->halls()->delete();
        $restaurant->delete();

        return redirect()
            ->route('admin.restaurants.index')
            ->with('success', 'Ресторан удалён.');
    }

    /**
     * Подготовка данных: обрабатывает чекбоксы и null-поля.
     */
    private function prepareData(Request $request): array
    {
        $checkboxes = [
            'karaoke', 'wifi', 'dance_floor', 'air_conditioner',
            'wardrobe', 'dressing_rooms', 'kids_room', 'kids_menu', 'kids_corner',
        ];

        $data = [];

        foreach ($checkboxes as $cb) {
            $data[$cb] = $request->filled($cb) ? 'в наличии' : null;
        }

        // Текстовые поля, которые могут быть null
        $textFields = [
            'banquet_menu', 'banquet_note', 'service_fee', 'own_alcohol',
            'bring_with_you', 'prepayment', 'actions',
            'textile_package', 'textile_colors', 'tables',
            'parking', 'pier',
            'own_equipment', 'projector_screen', 'music_stage',
            'sound_equipment', 'light_equipment', 'dimming_system',
            'interior', 'location', 'working_hours', 'website',
        ];

        foreach ($textFields as $field) {
            $value = $request->input($field);
            $data[$field] = $value !== '' ? $value : null;
        }

        return $data;
    }

}
