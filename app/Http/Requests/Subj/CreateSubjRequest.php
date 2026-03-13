<?php

namespace App\Http\Requests\Subj;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSubjRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Для продакшена рекомендуется заменить на реальную проверку прав пользователя
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'obj_id' => [
                'required',
                'integer',
                'min:1',
                'exists:objs,id'
            ],
            'name_subj' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s\d\pP]+$/u' // только буквы, цифры, пробелы и знаки препинания
            ],
            'capacity_from' => [
                'required',
                'integer',
                'min:0',
                'lte:capacity_to'
            ],
            'minimum_cost' => [
                'required',
                'integer',
                'min:0'
            ],
            'per_person' => [
                'required',
                'integer',
                'min:0'
            ],
            'capacity_to' => [
                'required',
                'integer',
                'min:0',
                'gte:capacity_from'
            ],
            'furshet' => [
                'required',
                'integer',
            ],
            'site_type' => [
                'required',
                'array',
                'min:1',
                'max:5'
            ],
            'site_type.*' => [
                'required',
                'string',
                'in:Ресторан,Кафе,Клуб,Лофт,Загородный дом,Банкетный зал,Терраса,Шатер,Яхта,Теплоход,База отдыха',
            ],
            'for_events.*' => [
                'string',

            ],
            'features' => [
                'required',
                'array',
                'min:1',
                'max:10'
            ],
            'features.*' => [
                'required',
                'string',
                'max:100',
            ],
            'text_subj' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Customize the error messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            // Общие сообщения
            'required' => 'Поле :attribute обязательно для заполнения',
            'string' => 'Поле :attribute должно быть строкой',
            'integer' => 'Поле :attribute должно быть целым числом',
            'array' => 'Поле :attribute должно быть массивом',
            'max' => 'Поле :attribute не должно превышать :max символов',
            'min' => 'Значение поля :attribute должно быть не менее :min',

            // Специфические сообщения
            'obj_id.exists' => 'Указанный объект не существует в системе',
            'obj_id.min' => 'ID объекта должен быть положительным числом',

            'name_subj.regex' => 'Название может содержать только буквы, цифры, пробелы и знаки препинания',

            'capacity_from.lte' => 'Вместимость от не может быть больше вместимости до',
            'capacity_to.gte' => 'Вместимость до не может быть меньше вместимости от',

            'furshet.between' => 'Значение фуршета должно быть 0 или 1',

            'site_type.min' => 'Необходимо выбрать хотя бы один тип площадки',
            'site_type.max' => 'Можно выбрать не более 5 типов площадки',
            'site_type.*.in' => 'Выбранный тип площадки недопустим',

            'features.min' => 'Необходимо указать хотя бы одну особенность',
            'features.max' => 'Можно указать не более 10 особенностей',
        ];
    }

    /**
     * Customize the attribute names for validation messages.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'obj_id' => 'ID объекта',
            'name_subj' => 'Название субъекта',
            'minimum_cost' => 'Минимальная стоимость',
            'per_person' => 'Стоимость за человека',
            'capacity_from' => 'Вместимость от',
            'capacity_to' => 'Вместимость до',
            'furshet' => 'Вместимость на фуршет',
            'features' => 'Особенности',
            'site_type' => 'Тип площадки',
            'text_subj' => 'Описание',
        ];
    }

}
