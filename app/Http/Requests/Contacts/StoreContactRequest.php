<?php

namespace App\Http\Requests\Contacts;

use App\Models\UserContact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'  => ['required', Rule::in(UserContact::TYPES)],
            'value' => ['required', 'string', 'max:255'],
        ];
    }
}