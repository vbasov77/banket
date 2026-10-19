<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

class UpdatePerformerRequest extends StorePerformerRequest
{
    public function authorize(): bool
    {
        return true; // владелец проверяется в контроллере через findOwnedByUser
    }
}
