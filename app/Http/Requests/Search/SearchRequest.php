<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для выполнения этого запроса.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Получить правила валидации для запроса.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'for_events' => 'nullable|string',
            'district' => 'nullable|array',
            'capacity_to' => 'nullable|integer',
            'per_person' => 'nullable|integer',
            'features' => 'nullable|array',
        ];
    }
}
