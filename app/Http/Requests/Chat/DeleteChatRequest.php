<?php

namespace App\Http\Requests\Chat;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class DeleteChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Здесь можно вынести проверку прав, но чаще её оставляют в контроллере
        return true;
    }

    public function rules(): array
    {
        return [
            'from_user_id' => ['required', 'integer', 'min:1'],
            'to_user_id'   => ['required', 'integer', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Чтобы возвращать JSON вместо редиректа при ошибке валидации
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Некорректные данные',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
