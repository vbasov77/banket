<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subj extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['obj_id',
        'name_subj',
        'minimum_cost',
        'per_person',
        'capacity_to',
        'furshet',
        'site_type',
        'features',
        'text_subj',
        'published',
    ];

    protected $casts = [
        'site_type' => 'array',
        'features' => 'array',
    ];


    /**
     * Проверяет, является ли пользователь автором (владельцем) через связанного Obj
     *
     * @param \App\Models\User|null $user
     * @return bool
     */
    public function isAuthor($user = null): bool
    {
        // Если пользователь не передан, берём текущего авторизованного
        $user = $user ?? auth()->user();

        // Если пользователя нет (не авторизован) — доступ запрещён
        if (!$user) {
            return false;
        }

        // Админ имеет доступ всегда
        if ($user->isAdmin()) {
            return true;
        }

        // Загружаем связь obj, если она не загружена
        if (!$this->relationLoaded('obj')) {
            $this->load('obj');
        }

        // Проверяем, что obj существует и его user_id совпадает с текущим пользователем
        $obj = $this->obj;

        return $obj && (int)$obj->user_id == (int)$user->id;
    }

    public function firstPhoto()
    {
        return $this->hasOne(ImgSubj::class, 'subj_id', 'id')
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->take(1);
    }

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
        return $this->hasOneThrough(
            GroupAddressObj::class,
            AddressSubj::class,
            'subj_id',  // FK в AddressSubj
            'id',       // FK в GroupAddressObj
            'id',       // Local key в Subj
            'group_id'  // Local key в AddressSubj
        );
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function detailObj()
    {
        return $this->hasOne(DetailsObj::class, 'obj_id', 'obj_id');
    }

    public function districts()
    {
        return $this->belongsToMany(District::class, 'group_address_objs', 'subj_id', 'district_id');
    }

    public function district()
    {
        return $this->belongsToThrough(
            District::class,
            [AddressSubj::class],
            ['subj_id', 'district_id'],
            ['id', 'id']
        );
    }

}

