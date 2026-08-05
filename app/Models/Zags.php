<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zags extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city_id',
        'latitude',
        'longitude',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    // Формула гаверсинуса для расчёта расстояния в км
    public static function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0;

        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) ** 2 +
            cos($lat1Rad) * cos($lat2Rad) * (sin($deltaLon / 2) ** 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function distanceTo(self $other): float
    {
        if (!$this->latitude || !$this->longitude || !$other->latitude || !$other->longitude) {
            return 0.0;
        }

        return self::distanceKm(
            (float)$this->latitude,
            (float)$this->longitude,
            (float)$other->latitude,
            (float)$other->longitude
        );
    }
}
