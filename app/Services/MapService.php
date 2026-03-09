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


    function parseAddress(string $addressString): array
    {
        // Разбиваем строку по разделителю-запятой и убираем пробелы
        $parts = array_map('trim', explode(',', $addressString));

        // Инициализируем результат с пустыми значениями
        $result = [
            'область' => '',
            'населённый_пункт' => '',
            'район' => '',
            'улица' => '',
            'дом' => ''
        ];

        // Шаг 1. Ищем дом — он обычно в начале строки и содержит цифры
        foreach ($parts as $index => $part) {
            if (preg_match('/\d+/', $part)) {
                $result['дом'] = $part;
                unset($parts[$index]);
                break;
            }
        }

        // Переиндексируем массив после удаления элемента
        $parts = array_values($parts);

        // Шаг 2. Обрабатываем оставшиеся части
        foreach ($parts as $part) {
            // Пропускаем почтовый индекс и страну
            if (preg_match('/^\d{6}$/', $part) || $part === 'Россия') {
                continue;
            }

            // Определяем область
            if (str_ends_with($part, 'область')) {
                $result['область'] = $part;
                continue;
            }

            // Определяем район
            if (str_contains($part, 'район') || str_contains($part, 'р-н')) {
                $result['район'] = $part;
                continue;
            }

            // Улучшенное определение улицы — расширенный список маркеров
            $streetMarkers = [
                'улица', 'ул.', 'ул',
                'шоссе', 'ш.',
                'проспект', 'пр-кт', 'пр.',
                'переулок', 'пер.',
                'бульвар', 'б-р',
                'набережная', 'наб.',
                'проезд', 'пр-д',
                'аллея', 'ал.',
                'площадь', 'пл.'
            ];

            foreach ($streetMarkers as $marker) {
                if (str_contains($part, $marker)) {
                    if (!empty($result['улица'])) {
                        $result['улица'] .= ', ' . $part;
                    } else {
                        $result['улица'] = $part;
                    }
                    continue 2; // переходим к следующей части адреса
                }
            }

            // Определение населённого пункта — только если ещё не заполнен
            if (!$result['населённый_пункт']) {
                // Исключаем части, которые точно не являются населённым пунктом
                $excludePatterns = [
                    'микрорайон', 'мкр.', 'м-н',
                    'поселение', 'пос.',
                    'округ', 'окр.',
                    'территория', 'тер.'
                ];
                $isExcluded = false;
                foreach ($excludePatterns as $pattern) {
                    if (str_contains($part, $pattern)) {
                        $isExcluded = true;
                        break;
                    }
                }

                if (!$isExcluded) {
                    $result['населённый_пункт'] = $part;
                }
            }
        }

        return $result;
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


}