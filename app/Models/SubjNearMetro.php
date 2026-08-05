<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjNearMetro extends Model
{
    protected $table = 'subj_near_metro'; // явно указываем таблицу (на случай, если имя не совпадает)

    protected $fillable = [
        'subj_id',
        'metro_station_id',
        'distance_km',
        'rank',
    ];

    protected $casts = [
        'distance_km' => 'decimal:3',
        'rank'        => 'integer',
    ];

    /**
     * Связь: запись принадлежит субъекту
     */
    public function subj(): BelongsTo
    {
        return $this->belongsTo(Subj::class, 'subj_id');
    }

    /**
     * Связь: запись принадлежит станции метро
     */
    public function metroStation(): BelongsTo
    {
        return $this->belongsTo(MetroStation::class, 'metro_station_id');
    }
}
