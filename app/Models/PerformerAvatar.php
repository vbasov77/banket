<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformerAvatar extends Model
{
    protected $fillable = ['performer_id', 'user_id', 'path'];
}

