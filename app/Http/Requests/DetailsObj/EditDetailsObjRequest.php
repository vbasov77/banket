<?php

declare(strict_types=1);

namespace App\Http\Requests\DetailsObj;

use Illuminate\Foundation\Http\FormRequest;

class EditDetailsObjRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'obj_id' => ['required', 'integer', 'exists:objs,id'],
            'service' => ['nullable', 'array', 'min:1'],
            'service.*' => ['string', 'max:1000'],
            'for_events' => ['required', 'array', 'min:1'],
            'for_events.*' => ['string', 'max:1000'],
            'kitchen' => ['nullable', 'array'],
            'kitchen.*' => ['string', 'max:50'],
            'alcohol' => 'nullable|in:0,1,2',
            'alcohol_price' => 'nullable|numeric|min:0|max:100000',
            'payment_methods' => ['nullable', 'array', 'min:1'],
            'payment_methods.*' => ['string', 'max:1000'],
            'service_fee' => 'nullable|numeric|min:0',
            'description' => ['nullable', 'string', 'min:10', 'max:1500'],
            'text_obj' => ['nullable', 'string', 'min:10', 'max:10000'],
            'bring_with_you' => ['nullable', 'array'],
            'bring_with_you.*' => ['string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'obj_id' => 'ID объекта',
            'kitchen' => 'Кухня',
            'for_events' => 'Для мероприятий',
            'service' => 'Сервис',
            'alcohol_status' => 'Пробковый сбор (статус)',
            'alcohol_price' => 'Цена пробкового сбора',
            'payment_methods' => 'Способ оплаты',
            'service_fee' => 'Сервисный сбор',
            'text_obj' => 'Описание',
            'bring_with_you' => 'Можно принести с собой',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле :attribute обязательно для заполнения',
            'integer' => 'Поле :attribute должно быть целым числом',
            'string' => 'Поле :attribute должно быть строкой',
            'array' => 'Поле :attribute должно быть массивом',
            'boolean' => 'Поле :attribute должно содержать логическое значение',
            'min' => 'Поле :attribute должно содержать не менее :min символов',
            'max' => 'Поле :attribute не должно превышать :max символов',
            'in' => 'Выбранное значение для :attribute недопустимо',
            'exists' => 'Указанный объект не существует',
            'numeric' => 'Поле :attribute должно быть числом',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('alcohol') == 2) {
                $price = $this->input('alcohol_price');
                if (!$price || $price <= 0) {
                    $validator->errors()->add('alcohol_price', 'Цена должна быть указана и быть больше нуля, если выбран вариант "За отдельную плату".');
                }
            }
        });
    }
}
