<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressSubj extends Model
{
    use HasFactory;

    protected $fillable = [
        'subj_id',
        'group_id',
        'address',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'address' => 'array',
    ];

    public function obj()
    {
        return $this->belongsTo(Obj::class, 'obj_id');
    }

    public function group()
    {
        return $this->belongsTo(GroupAddressObj::class, 'group_id');
    }

    // Добавляем недостающую связь
    public function subj()
    {
        return $this->belongsTo(Subj::class, 'subj_id');
    }
}

