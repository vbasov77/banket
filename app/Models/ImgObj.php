<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImgObj extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'img_obj';

    protected $fillable = ['obj_id', 'photo_id','group_id', 'path'];

    /**
     * @return BelongsTo
     */
    public function obj(): BelongsTo
    {
        return $this->belongsTo(Obj::class, 'obj_id', 'id');
    }
}
