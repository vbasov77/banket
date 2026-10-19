<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User; // убедись, что путь правильный

class Performer extends Model
{
    protected $fillable = [
        'category_id', 'user_id', 'city_id', 'name', 'description',
        'phone', 'video_url', 'published',
    ];

    protected $casts = [
        'price_from' => 'integer',
        'video_url' => 'array',
    ];

    /**
     * Проверяет, является ли пользователь автором (владельцем) исполнителя
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

    public function category()
    {
        return $this->belongsTo(CatalogCategory::class);
    }

    public function photos()
    {
        return $this->hasMany(PerformerPhoto::class)->orderBy('position');
    }

    public function avatar()
    {
        return $this->hasOne(PerformerAvatar::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar?->path ?? asset('images/no-photo.svg');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCityNameAttribute(): string
    {
        return $this->city?->name ?? '';
    }

    public function firstPhoto(): HasOne
    {
        return $this->hasOne(PerformerPhoto::class, 'performer_id')
            ->orderBy('position', 'asc')
            ->select('performer_id', 'big_img', 'small_img', 'position');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(PerformerPackage::class)
            ->orderBy('sort')
            ->orderBy('id');
    }

    public function getMinPriceAttribute(): ?int
    {
        return $this->packages
            ->where('is_addon', false)
            ->map(fn ($p) => $p->price_min ?? $p->price_max)
            ->filter(fn ($price) => $price !== null)
            ->min();
    }
}
