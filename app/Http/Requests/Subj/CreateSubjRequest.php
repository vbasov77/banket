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
                'nullable',
                'string',
                'max:30',
                'regex:/^[\pL\s\d\pP]+$/u' // только буквы, цифры, пробелы и знаки препинания
            ],
            'minimum_cost' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'per_person' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'capacity_to' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'furshet' => [
                'nullable',
                'integer',
            ],
            'site_type' => [
                'required',
                'array',
                'min:1',
                'max:5'
            ],
            'site_type.*' => ['string', 'max:1000'],
            'loud_music_until' => [
                'nullable',
                'string',
                Rule::in(['22:00', '23:00', '00:00', '01:00', 'morning']),
            ],

            'features' => ['nullable', 'string', 'max:10000'],
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
            'capacity_to.gte' => 'Вместимость до не может быть меньше вместимости от',

            'furshet.between' => 'Значение фуршета должно быть 0 или 1',

            'site_type.min' => 'Необходимо выбрать хотя бы один тип площадки',
            'site_type.max' => 'Можно выбрать не более 5 типов площадки',
            'site_type.*.in' => 'Выбранный тип площадки недопустим',
            'loud_music_until.in' => 'Выберите допустимое время, когда разрешена громкая музыка',

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
            'capacity_to' => 'Вместимость до',
            'furshet' => 'Вместимость на фуршет',
            'site_type' => 'Тип площадки',
            'loud_music_until.in' => 'Громкая музыка',
            'features' => 'Особенности'
        ];
    }

}
