<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Если хочешь оставить без временных меток — ок, можно так:
    public $timestamps = false;

    /**
     * Связь «одна роль — много пользователей»
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
