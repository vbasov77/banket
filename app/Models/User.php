<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * @param int $userId
     * @return bool
     */
    public function isAuthor(int $userId): bool
    {
        return $this->id == $userId;
    }

    /**
     * @return mixed
     */
    public function myObjId(): mixed
    {
        return Obj::where('user_id', $this->id)->value('id');
    }

    /**
     * @return HasMany
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(FavoriteSubj::class);
    }

    /**
     * @return BelongsToMany
     */
    public function favoriteRestaurants(): BelongsToMany
    {
        return $this->belongsToMany(Subj::class, 'favorites_subj');
    }

    /**
     * @return BelongsToMany
     */
    public function city(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'user_city');
    }

}
