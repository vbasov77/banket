<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformerAvatar extends Model
{
    public $timestamps = false;

    protected $fillable = ['performer_id', 'user_id', 'small_id', 'small_img'];
}

