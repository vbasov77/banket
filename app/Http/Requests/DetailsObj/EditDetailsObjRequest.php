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
            'id' => ['required', 'integer'],
            'obj_id' => ['required', 'integer', 'exists:objs,id'],
            'for_events' => ['required', 'array', 'min:1'],
            'for_events.*' => [
                'string',
                'in:Свадьба,День рождения,Корпоратив,Выпускной,Детский праздник,Фуршет,Мальчишник/Девичник'
            ],
            'kitchen' => ['required', 'array'],
            'kitchen.*' => ['string', 'max:50'],
            'alcohol' => 'required|in:0,1,2',
            'alcohol_price' => 'nullable|numeric|min:0|max:100000',
            'more' => 'required|in:0,1,2',
            'more_price' => 'nullable|numeric|min:0|max:100000',
            'payment_methods' => ['required', 'array', 'min:1'],
            'payment_methods.*' => ['string', 'in:Наличные,Карта,Перевод'],
            'text_obj' => ['required', 'string', 'min:10', 'max:10000'],
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
            'more_status' => 'Дополнительно (статус)',
            'more_price' => 'Цена дополнительных услуг',
            'payment_methods' => 'Способ оплаты',
            'text_obj' => 'Описание',
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
