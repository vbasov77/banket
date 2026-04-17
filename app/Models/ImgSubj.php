<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImgSubj extends Model
{
    use HasFactory;

    protected $table = 'img_subj';
    public $timestamps = false;
    protected $fillable = ['subj_id', 'photo_id', 'path', 'position'];
}
