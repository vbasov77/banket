<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformerPackage extends Model
{
    protected $fillable = [
        'performer_id',
        'title',
        'description',
        'price_min',
        'price_max',
        'is_addon',
        'sort',
    ];

    public function performer(): BelongsTo
    {
        return $this->belongsTo(Performer::class);
    }
}
