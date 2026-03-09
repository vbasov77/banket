<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressObj extends Model
{
    use HasFactory;

    protected $fillable = [
        'obj_id',
        'address',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'address' => 'array',
    ];
}
