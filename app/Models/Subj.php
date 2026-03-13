<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subj extends Model
{
    public $timestamps = false;
    protected $fillable = ['obj_id',
        'name_subj',
        'minimum_cost',
        'per_person',
        'capacity_from',
        'capacity_to',
        'furshet',
        'site_type',
        'features',
        'text_subj',
    ];

    protected $casts = [
        'site_type' => 'array',
        'features' => 'array',
    ];

    public function imgSubjFirst()
    {
        return $this->hasOne(ImgSubj::class, 'subj_id', 'id')
            ->orderBy('position', 'asc');
    }

    public function addressSubj()
    {
        return $this->hasOne(AddressSubj::class, 'subj_id', 'id');
    }

    public function imgSubjsWithLimit()
    {
        return $this->hasMany(ImgSubj::class, 'subj_id', 'id')
            ->orderBy('position', 'asc')
            ->limit(5); // берём не более 5 записей
    }

    public function obj()
    {
        return $this->belongsTo(Obj::class, 'obj_id');
    }

    public function imgSubjs()
    {
        return $this->hasMany(ImgSubj::class, 'subj_id')
            ->orderBy('position', 'asc');
    }

    public function primaryImg()
    {
        return $this->hasOne(ImgSubj::class, 'subj_id', 'id')
            ->orderBy('position', 'asc')   // сначала по position
            ->orderBy('id', 'asc');      // страховка: если position одинаковые
    }

    public function groupAddressObj()
    {
        return $this->hasOne(GroupAddressObj::class, 'subj_id');
    }



}

