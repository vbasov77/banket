<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Obj extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'name_obj', 'phone_obj',];

    protected $table = 'objs';

    /**
     * Проверяет, является ли пользователь автором (владельцем) объекта
     *
     * @param \App\Models\User|null $user
     * @return bool
     */
    public function isAuthor($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // Админ имеет доступ всегда
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $this->user_id === (int) $user->id;
    }

    /**
     * @return HasOne
     */
    public function detailsObj(): HasOne
    {
        return $this->hasOne(DetailsObj::class, 'obj_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function addressObj(): HasMany
    {
        return $this->hasOne(AddressObj::class, 'obj_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subj::class, 'obj_id');
    }

    /**
     * @return HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailsObj::class, 'obj_id');
    }

    /**
     * @return HasMany
     */
    public function subjs(): HasMany
    {
        return $this->hasMany(Subj::class, 'obj_id', 'id')
            ->where('published', '=', 1);
    }

    /**
     * @return HasOne
     */
    public function firstImgSubj(): HasOne
    {
        return $this->hasOne(ImgSubj::class, 'subj_id', 'id')
            ->orderBy('position', 'asc');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function imgObj(): HasOne
    {
        return $this->hasOne(ImgObj::class, 'obj_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function groupAddressObjs(): HasMany
    {
        return $this->hasMany(GroupAddressObj::class, 'obj_id');
    }

    /**
     * @return BelongsToMany
     */
    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'group_address_objs', 'obj_id', 'district_id')
            ->withPivot(['city_id', 'address', 'latitude', 'longitude']);
    }

}
