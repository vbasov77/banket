<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailsObj extends Model
{
    protected $table = 'details_obj';
    public $timestamps = false;
    protected $fillable = [
        'obj_id',
        'for_events',
        'kitchen',
        'service',
        'alcohol',
        'payment_methods',
        'text_obj',
    ];

    protected $casts = [
        'for_events' => 'array',
        'kitchen' => 'array',
        'service' => 'array',
        'payment_methods' => 'array',
        ];

    public function obj()
    {
        return $this->belongsTo(Obj::class, 'obj_id');
    }
}
