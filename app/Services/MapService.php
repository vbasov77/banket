<?php


namespace App\Services;


use App\Repositories\MapRepository;
use Illuminate\Http\JsonResponse;

class MapService extends Service
{

    private $mapRepository;


    public function __construct()
    {
        $this->mapRepository = new MapRepository();
    }


    public function getMapData(): JsonResponse
    {
        return $this->mapRepository->getMapData();
    }

    function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // в метрах

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function findMap()
    {
        return $this->mapRepository->findMap();
    }


}