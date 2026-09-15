<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ip extends Model
{
    use HasFactory;

    protected $table = 'ips';

    // Только эти поля можно массово присваивать (если используешь $model->fill())
    protected $fillable = [
        'ip',
        'created_at',
    ];

    // Если хочешь, чтобы created_at автоматически ставился при создании — оставь как есть.
    // Laravel сам подставит текущее время, если поле nullable или имеет DEFAULT.
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
