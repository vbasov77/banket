<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlbumsVk extends Model
{
    use HasFactory;

    protected $table = 'albums_vk';

    protected $fillable = [
        'city_id',
        'city_name',
        'album_id',
        'group_id'
    ];

    protected $casts = [
        'album_id' => 'integer',
        'group_id' => 'integer',
        'city_id' => 'integer'
    ];
}
