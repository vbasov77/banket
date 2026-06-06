<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVk extends Model
{
    use HasFactory;

    protected $table = 'users_vk';

    protected $fillable = [
        'vk_id',
        'user_id',
        'first_name',
        'last_name',
        'bdate',
        'photo',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];
}
