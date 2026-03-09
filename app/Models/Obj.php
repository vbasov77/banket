<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obj extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'name_obj', 'address_obj', 'phone_obj',];

    protected $table = 'objs';

    public function detailsObj() {
        return $this->hasOne(DetailsObj::class, 'obj_id', 'id');
    }

    public function addressObj() {
        return $this->hasOne(AddressObj::class, 'obj_id', 'id');
    }

    public function subjects()
    {
        return $this->hasMany(Subj::class, 'obj_id');
    }

    public function details()
    {
        return $this->hasMany(DetailsObj::class, 'obj_id');
    }

    public function subjsAll() {
        return $this->hasMany(Subj::class, 'obj_id', 'id');
    }

    public function subjs() {
        return $this->hasMany(Subj::class, 'obj_id', 'id')
            ->where('published', '=', 1);
    }

    public function firstImgSubj() {
        return $this->hasOne(ImgSubj::class, 'subj_id', 'id')
            ->orderBy('position', 'asc');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function imgObj()
    {
        return $this->hasOne(ImgObj::class, 'obj_id', 'id');
    }

    public function addressSubjs()
    {
        return $this->hasMany(AddressSubj::class, 'obj_id');
    }

}
