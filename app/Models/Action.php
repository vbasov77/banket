<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Action extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'actions';

    protected $fillable = [
        'obj_id',
        'actions',
    ];

    public function obj(): BelongsTo
    {
        return $this->belongsTo(Obj::class, 'obj_id');
    }
}
