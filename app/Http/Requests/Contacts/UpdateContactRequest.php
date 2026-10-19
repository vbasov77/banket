<?php

namespace App\Http\Requests\Contacts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\UserContact;

class UpdateContactRequest extends StoreContactRequest
{
    // правила те же, что и при создании
}