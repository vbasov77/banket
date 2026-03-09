<?php

declare(strict_types=1);

namespace App\Http\Requests\Obj;

use Illuminate\Foundation\Http\FormRequest;

class CreateObjRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "user_id" => ['required'],
            "name_obj" => ['required', 'string', 'min:2', 'max:200'],
            "phone_obj" => ['required', 'string', 'min:18', 'max:18'],
            "address_obj" => ['required', 'string', 'min:2', 'max:250'],
        ];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name_obj' => "Название",
            'phone_obj' => "Телефон",
            'address_obj' => "Адрес"
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'required' => "Поле :attribute обязательно для заполнения",
            'string' => "Поле :attribute должно быть строкой",
        ];
    }
}
