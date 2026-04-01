<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_city';

    protected $fillable = [
        'user_id',
        'city_id'
    ];
}
