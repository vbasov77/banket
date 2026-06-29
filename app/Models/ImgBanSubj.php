<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImgBanSubj extends Model
{
    use HasFactory;

    /**
     * Имя таблицы, связанной с моделью.
     *
     * @var string
     */
    protected $table = 'img_ban_subj';

    /**
     * Поля, которые можно массово заполнять.
     *
     * @var array<string>
     */
    protected $fillable = [
        'subj_id',
        'big_img',
        'big_id',
        'small_img',
        'small_id',
        'position',
    ];

    public $timestamps = false;

    /**
     * Получает полный URL изображения.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->path;
    }

    /**
     * Получает ID изображения от imageban.ru.
     *
     * @return string|null
     */
    public function getImageIdAttribute(): ?string
    {
        return $this->attributes['img_id'];
    }

    /**
     * Связь с родительской сущностью (опционально).
     * Раскомментируйте и настройте, если есть модель Subject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function subject()
    {
        return $this->belongsTo(Subj::class, 'subj_id');
    }


}
