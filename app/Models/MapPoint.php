<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapPoint extends Model
{
    use HasFactory;

    protected $fillable = ['address', 'latitude', 'longitude', 'subj_id'];

    protected $casts = [
        'address' => 'array',
    ];
}

