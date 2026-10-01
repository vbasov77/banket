<?php

declare(strict_types=1);

namespace App\Http\Requests\ObjFeatures;

use Illuminate\Foundation\Http\FormRequest;

class EditObjFeaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Если нужна проверка прав (например, только владелец объекта) — верни false и реализуй логику
        return true;
    }

    public function rules(): array
    {
        return [
            'obj_id' => 'required|exists:objs,id',
            'banquet_note' => 'nullable|string|max:1000',
            'prepayment' => 'nullable|string|max:255',
            'textile_package' => 'nullable|array',
            'textile_package.*' => 'string|max:100',
            'textile_colors' => 'nullable|string|max:255',
            'tables' => 'nullable|array',
            'tables.*' => 'string|max:100',
            'parking' => 'nullable|string|max:255',
            'pier' => 'nullable|string|max:255',
            'equipment' => 'nullable|array',
            'equipment.*' => 'string|max:255',
            'kids' => 'nullable|array',
            'kids.*' => 'string|max:255',
            'interior' => 'nullable|string|max:1000',
            'location' => 'nullable|array',
            'location.*' => 'string|max:100',
        ];
    }

    public function attributes(): array
    {
        return [
            'obj_id' => 'ID объекта',
            'banquet_note' => 'Примечание к банкету',
            'prepayment' => 'Предоплата',
            'textile_package' => 'Текстильный пакет',
            'textile_colors' => 'Цвета текстиля',
            'tables' => 'Столы',
            'parking' => 'Парковка',
            'pier' => 'Пирс / Причал',
            'equipment' => 'Оборудование',
            'kids' => 'Для детей',
            'interior' => 'Интерьер',
            'location' => 'Расположение',
        ];
    }

    public function messages(): array
    {
        return [
            'obj_id.required' => 'ID объекта обязателен.',
            'obj_id.exists' => 'Указанный объект не найден.',
            'banquet_note.max' => 'Примечание к банкету не должно превышать 1000 символов.',
            'prepayment.max' => 'Поле «Предоплата» не должно превышать 255 символов.',
            'textile_package.max' => 'Текстильный пакет не должен превышать 255 символов.',
            'textile_colors.max' => 'Цвета текстиля не должны превышать 255 символов.',
            'tables.max' => 'Описание столов не должно превышать 255 символов.',
            'parking.max' => 'Описание парковки не должно превышать 255 символов.',
            'pier.max' => 'Описание пирса не должно превышать 255 символов.',
            'equipment.array' => 'Поле «Оборудование» должно быть списком.',
            'equipment.*.string' => 'Каждый элемент списка «Оборудование» должен быть текстом.',
            'equipment.*.max' => 'Каждый элемент «Оборудование» не должен превышать 255 символов.',
            'kids.array' => 'Поле «Для детей» должно быть списком.',
            'kids.*.string' => 'Каждый элемент списка «Для детей» должен быть текстом.',
            'kids.*.max' => 'Каждый элемент «Для детей» не должен превышать 255 символов.',
            'interior.max' => 'Описание интерьера не должно превышать 1000 символов.',
            'location.max' => 'Расположение не должно превышать 255 символов.',
        ];
    }
}
