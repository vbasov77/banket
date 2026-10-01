<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ParserGorkoController extends Controller
{
    public function form()
    {
        return view('parsers.gorko.parser');
    }

    public function parse(Request $request)
    {
        $request->validate([
            'url_gorko' => 'required|url',
        ]);

        $url = $request->input('url_gorko');

        try {
            $html = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                        . 'AppleWebKit/537.36 (KHTML, like Gecko) '
                        . 'Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
                ])
                ->get($url)
                ->body();
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['url_gorko' => 'Не удалось загрузить страницу: ' . $e->getMessage()]);
        }

        $data = $this->extractGorkoData($html);

        return view('parsers.gorko.parser', [
            'url_gorko' => $url,
            'result' => $data,
        ]);
    }

    private function extractGorkoData(string $html): array
    {
        $debug = [];

        // 1. Извлекаем window.__initialData__
        $initialData = null;
        if (preg_match('/window\.__initialData__\s*=\s*({.+?})\s*;?\s*<\/script>/s', $html, $m)) {
            $jsonText = $m[1];
            $initialData = json_decode($jsonText, true);
            if (!$initialData) {
                $lastBrace = mb_strrpos($jsonText, '}');
                if ($lastBrace !== false) {
                    $initialData = json_decode(mb_substr($jsonText, 0, $lastBrace + 1), true);
                }
            }
        }

        if (!$initialData) {
            $htmlForText = preg_replace('/<\/(div|p|li|td|th|h[1-6]|span|a|button|section|article|label)>/i', ' ', $html);
            $htmlForText = preg_replace('/<(br|hr)[^>]*>/i', ' ', $htmlForText);
            $htmlForText = '<?xml encoding="UTF-8">' . $htmlForText;
            $dom = new \DOMDocument();
            @$dom->loadHTML($htmlForText, LIBXML_NOERROR | LIBXML_NOWARNING);
            $fullText = $this->cleanText($dom->textContent);
            $halls = $this->extractGorkoHalls($fullText);
            return ['_halls' => $halls, '_debug' => ['window.__initialData__ не найден, парсинг из текста: ' . count($halls) . ' залов']];
        }

        $debug[] = 'window.__initialData__ найден';

        // 2. venueId и entityData
        $venueId = null;
        $entityData = null;

        if (!empty($initialData['ProfileVenue']['loadEntity']['data'])) {
            $entityData = $initialData['ProfileVenue']['loadEntity']['data'];
            $venueId = array_key_first($entityData);
        }

        // Нормализация URL
        $normalizePhotoUrl = function (string $url): string {
            if (preg_match('/=w\d+/', $url) || preg_match('/=s\d+/', $url)) {
                return $url;
            }
            return $url . '=s0';
        };

        // 3. Глобальный словарь photoId → URL
        $photoLookup = [];

        // Источник 1: venue
        if ($venueId !== null && isset($initialData['ProfileVenue']['loadMedia']['data'][$venueId]['venue'])) {
            foreach ($initialData['ProfileVenue']['loadMedia']['data'][$venueId]['venue'] as $v) {
                if (is_array($v) && !empty($v['id']) && !empty($v['url'])) {
                    $photoLookup[$v['id']] = $normalizePhotoUrl($v['url']);
                }
            }
        }

        // Источник 2: album[].img
        $albums = [];
        if ($venueId !== null && isset($entityData[$venueId]['album'])) {
            $albums = $entityData[$venueId]['album'];
            foreach ($albums as $album) {
                if (is_array($album) && !empty($album['img']['id']) && !empty($album['img']['url'])) {
                    $photoLookup[$album['img']['id']] = $normalizePhotoUrl($album['img']['url']);
                }
            }
        }

        // Источник 3: room[][0]
        $mediaRooms = [];
        if ($venueId !== null && isset($initialData['ProfileVenue']['loadMedia']['data'][$venueId]['room'])) {
            $mediaRooms = $initialData['ProfileVenue']['loadMedia']['data'][$venueId]['room'];
            foreach ($mediaRooms as $roomPhotos) {
                if (is_array($roomPhotos) && !empty($roomPhotos[0]) && is_array($roomPhotos[0]) && !empty($roomPhotos[0]['url'])) {
                    $photoLookup[$roomPhotos[0]['id']] = $normalizePhotoUrl($roomPhotos[0]['url']);
                }
            }
        }

        $debug[] = "Словарь фото (стр. площадки): " . count($photoLookup) . " URL";

        // Источник 4: Загружаем страницы альбомов и собираем фото
        foreach ($albums as $album) {
            $albumId = $album['id'] ?? null;
            $albumTitle = $album['title'] ?? '';
            if (!$albumId) continue;

            // Пропускаем свадебные альбомы — они не относятся к залам
            if (!empty($album['isWedding'])) continue;

            $albumUrl = "https://spb.gorko.ru/album/{$albumId}";
            try {
                $albumHtml = Http::timeout(20)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                            . 'AppleWebKit/537.36 (KHTML, like Gecko) '
                            . 'Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
                        'Referer' => 'https://spb.gorko.ru/',
                    ])
                    ->get($albumUrl)
                    ->body();

                $albumData = null;
                if (preg_match('/window\.__initialData__\s*=\s*({.+?})\s*;?\s*<\/script>/s', $albumHtml, $am)) {
                    $albumJson = $am[1];
                    $albumData = json_decode($albumJson, true);
                    if (!$albumData) {
                        $lastB = mb_strrpos($albumJson, '}');
                        if ($lastB !== false) {
                            $albumData = json_decode(mb_substr($albumJson, 0, $lastB + 1), true);
                        }
                    }
                }

                if ($albumData && isset($albumData['Album']['loadMediaItems']['data']['media'])) {
                    $media = $albumData['Album']['loadMediaItems']['data']['media'];
                    $added = 0;
                    foreach ($media as $photo) {
                        if (is_array($photo) && !empty($photo['id'])) {
                            // original_url уже содержит =s0
                            $url = $photo['original_url'] ?? ($photo['preview_url']['b'] ?? '');
                            if ($url) {
                                $photoLookup[$photo['id']] = $normalizePhotoUrl($url);
                                $added++;
                            }
                        }
                    }
                    $debug[] = "Альбом «{$albumTitle}»: +{$added} фото";
                }
            } catch (\Exception $e) {
                $debug[] = "Альбом «{$albumTitle}»: ошибка — " . mb_substr($e->getMessage(), 0, 80);
            }
        }

        $debug[] = "Словарь фото (итого): " . count($photoLookup) . " URL";

        // 4. Залы
        $scheduleRooms = $initialData['ProfileVenue']['loadSchedule']['data']['rooms'] ?? [];
        $entityRooms = ($venueId !== null && isset($entityData[$venueId]['room'])) ? $entityData[$venueId]['room'] : [];

        $halls = [];

        foreach ($scheduleRooms as $scheduleRoom) {
            $hallId = $scheduleRoom['id'] ?? null;
            $hallName = trim($scheduleRoom['name'] ?? '');
            if (!$hallId || !$hallName) {
                continue;
            }

            // Данные зала из entity
            $entityRoom = null;
            foreach ($entityRooms as $er) {
                if (($er['id'] ?? null) == $hallId) {
                    $entityRoom = $er;
                    break;
                }
            }

            $capacity = '';
            $furshet = 0;
            $perPerson = 0;
            $minCost = 0;

            if ($entityRoom) {
                foreach ($entityRoom['param'] ?? [] as $param) {
                    $key = $param['key'] ?? '';
                    $value = $param['value'] ?? '';
                    $text = $param['text'] ?? '';

                    if ($key === 'param_capacity_reception') {
                        $furshet = (int) $value;
                    }
                    if ($key === 'capacity') {
                        $capacity = $text;
                    }
                    if ($key === 'param_banquet_min_price') {
                        $minCost = (int) preg_replace('/\D/', '', $value);
                    }
                }
                if (!empty($entityRoom['minPrice'])) {
                    $perPerson = (int) $entityRoom['minPrice'];
                }
            }

            // 5. Фото
            $photos = [];

// 5a. Обложка из room[hallId][0] — первое фото как на сайте
            if (isset($mediaRooms[$hallId][0]) && is_array($mediaRooms[$hallId][0]) && !empty($mediaRooms[$hallId][0]['url'])) {
                $photos[] = $normalizePhotoUrl($mediaRooms[$hallId][0]['url']);
            }

// 5b. Фото из matching-альбома в порядке альбома
            $matchingAlbum = null;
            foreach ($albums as $album) {
                if (mb_strtolower($album['title'] ?? '') === mb_strtolower($hallName)) {
                    $matchingAlbum = $album;
                    break;
                }
            }

            if ($matchingAlbum && !empty($matchingAlbum['media'])) {
                foreach ($matchingAlbum['media'] as $mediaId) {
                    if (count($photos) >= 10) break;
                    if (isset($photoLookup[$mediaId])) {
                        $url = $photoLookup[$mediaId];
                        if (!in_array($url, $photos)) {
                            $photos[] = $url;
                        }
                    }
                }
            }

// 5c. Если в альбоме >30 фото и мы получили <10 — грузим страницу 2
            if (count($photos) < 10 && $matchingAlbum) {
                $albumCount = $matchingAlbum['countMedia'] ?? 0;
                if ($albumCount > 30) {
                    $albumId = $matchingAlbum['id'];
                    $page2Url = "https://spb.gorko.ru/album/{$albumId}?page=2";
                    try {
                        $page2Html = Http::timeout(20)
                            ->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                                    . 'AppleWebKit/537.36 (KHTML, like Gecko) '
                                    . 'Chrome/120.0.0.0 Safari/537.36',
                                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                                'Referer' => 'https://spb.gorko.ru/',
                            ])
                            ->get($page2Url)
                            ->body();

                        $page2Data = null;
                        if (preg_match('/window\.__initialData__\s*=\s*({.+?})\s*;?\s*<\/script>/s', $page2Html, $p2m)) {
                            $p2Json = $p2m[1];
                            $page2Data = json_decode($p2Json, true);
                            if (!$page2Data) {
                                $lastB = mb_strrpos($p2Json, '}');
                                if ($lastB !== false) {
                                    $page2Data = json_decode(mb_substr($p2Json, 0, $lastB + 1), true);
                                }
                            }
                        }

                        if ($page2Data && isset($page2Data['Album']['loadMediaItems']['data']['media'])) {
                            foreach ($page2Data['Album']['loadMediaItems']['data']['media'] as $photo) {
                                if (count($photos) >= 10) break;
                                if (is_array($photo) && !empty($photo['original_url'])) {
                                    $url = $normalizePhotoUrl($photo['original_url']);
                                    if (!in_array($url, $photos)) {
                                        $photos[] = $url;
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // тихо игнорируем
                    }
                }
            }

// 5d. Если совсем мало — добираем из room ID (могут совпасть с venue)
            if (count($photos) < 3 && isset($mediaRooms[$hallId])) {
                foreach ($mediaRooms[$hallId] as $item) {
                    if (count($photos) >= 10) break;
                    if (is_int($item) && isset($photoLookup[$item])) {
                        $url = $photoLookup[$item];
                        if (!in_array($url, $photos)) {
                            $photos[] = $url;
                        }
                    }
                }
            }

            $photos = array_slice($photos, 0, 10);

            $halls[] = [
                'id'           => $hallId,
                'name'         => $hallName,
                'capacity'     => $capacity,
                'furshet'      => $furshet,
                'per_person'   => $perPerson,
                'minimum_cost' => $minCost,
                'photos'       => $photos,
            ];

            $debug[] = "Зал: {$hallName} | capacity={$capacity} | furshet={$furshet} | perPerson={$perPerson} | photos=" . count($photos);
        }

        return [
            '_halls' => $halls,
            '_debug' => $debug,
        ];
    }

    private function extractGorkoHalls(string $fullText): array
    {
        $halls = [];
        $roomsStart = mb_strpos($fullText, 'помещени');
        if ($roomsStart === false) return [];

        $endMarkers = ['ОписаниеРесторанный', 'ОписаниеРесторан', 'Тип площадки', 'АльбомыОтзывы', 'Альбомы'];
        $end = mb_strlen($fullText);
        foreach ($endMarkers as $marker) {
            $pos = mb_strpos($fullText, $marker, $roomsStart + 10);
            if ($pos !== false && $pos < $end) $end = $pos;
        }

        $hallsText = mb_substr($fullText, $roomsStart, $end - $roomsStart);
        $blocks = preg_split('/Подходит для вас/u', $hallsText);

        for ($i = 1; $i < count($blocks); $i++) {
            $block = $blocks[$i];
            $nameEnd = mb_strpos($block, 'Свадьба');
            if ($nameEnd === false) $nameEnd = mb_strpos($block, 'Тип');
            if ($nameEnd === false) $nameEnd = mb_strpos($block, 'Проверьте');

            $rawName = $nameEnd !== false ? mb_substr($block, 0, $nameEnd) : '';
            $rawName = preg_replace('/^\d+\s*помещени[яй]\s*/u', '', $rawName);
            $rawName = preg_replace('/^Подробнее\s*/u', '', $rawName);
            $rawName = trim($rawName);

            $furshet = 0;
            if (preg_match('/Вместимость на фуршет\s*(\d+)/uis', $block, $m)) $furshet = (int) $m[1];

            $capFrom = 0; $capTo = 0;
            if (preg_match('/Вместимость\s*(\d+)\s*[–-]\s*(\d+)/uis', $block, $m)) {
                $capFrom = (int) $m[1]; $capTo = (int) $m[2];
            }

            $perPerson = 0;
            if (preg_match('/Стоимость\s*от\s+([\d\s]+)\s*₽/uis', $block, $m)) $perPerson = (int) preg_replace('/\s+/', '', $m[1]);

            $minCost = 0;
            if (preg_match('/Минимальная стоимость банкета\s*([\d\s]+)\s*₽/uis', $block, $m)) $minCost = (int) preg_replace('/\s+/', '', $m[1]);

            $halls[] = [
                'name'         => $rawName !== '' ? $rawName : ('Зал ' . $i),
                'capacity'     => $capFrom . '–' . $capTo,
                'furshet'      => $furshet,
                'per_person'   => $perPerson,
                'minimum_cost' => $minCost,
                'photos'       => [],
            ];
        }

        return $halls;
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }
}
