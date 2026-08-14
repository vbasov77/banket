<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjNearMetro extends Model
{
    protected $table = 'subj_near_metro';

    protected $fillable = [
        'subj_id',
        'metro_station_id',
        'distance_meters',
        'rank',
    ];


    public function subj(): BelongsTo
    {
        return $this->belongsTo(Subj::class, 'subj_id'); // убедись, что модель Subj существует
    }

    public function metroStation(): BelongsTo
    {
        return $this->belongsTo(MetroStation::class, 'metro_station_id');
    }
}
