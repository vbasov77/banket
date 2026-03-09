<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImgObj extends Model
{
    public $timestamps = false;

    protected $table = 'img_obj';

    protected $fillable = ['obj_id', 'photo_id', 'path'];

    public function obj()
    {
        return $this->belongsTo(Obj::class, 'obj_id', 'id');
    }
}
