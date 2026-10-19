<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformerPhoto extends Model
{
    public $timestamps = false;

    protected $fillable = ['performer_id', 'user_id', 'big_id', 'big_img', 'small_id', 'small_img', 'position'];

    public function performer(): BelongsTo
    {
        return $this->belongsTo(Performer::class);
    }

}
