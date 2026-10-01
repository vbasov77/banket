<?php

namespace App\Services;

use App\Models\Action;
use App\Repositories\ParserABRepository;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParserABService
{
    public function __construct(
        private ParserABRepository $repository,
        private ImgSubjService     $imgSubjService,
    )
    {
    }

    // ============================================================
    //  PUBLIC: Парсинг
    // ============================================================

    /**
     * Полный цикл парсинга: avtobanket + опционально gorko.
     */
    public function processData(string $abUrl, ?string $gorkoUrl = null): array
    {
        $html = $this->fetchHtml($abUrl);
        $data = $this->extractData($html);

        if (!empty($gorkoUrl)) {
            $gorkoData = $this->fetchGorkoData($gorkoUrl);
            $data = $this->mergeGorkoData($data, $gorkoData);
        }

        return $data;
    }

    // ============================================================
    //  PUBLIC: Сохранение в БД
    // ============================================================

    /**
     * Сохранение распарсенных данных: obj → details → features → subjs + фото.
     */
    public function storeParsedData(array $data, int $userId): ?int
    {
        $objId = null;

        DB::transaction(function () use ($data, $userId, &$objId) {
            // === objs ===
            $objId = $this->repository->createObj([
                'user_id' => $userId,
                'name_obj' => $data['name_obj'] ?? '',
                'phone_obj' => $data['phone_obj'] ?? '',
                'created_at' => now(),
            ]);

            if (!$objId) {
                throw new \RuntimeException('Не удалось создать объект (objs)');
            }


            // === details_obj ===
            $this->repository->createDetailsObj([
                'obj_id' => $objId,
                'for_events' => $this->explodeToJson($data['for_events'] ?? null),
                'kitchen' => $this->explodeToJson($data['kitchen'] ?? null),
                'service' => $this->explodeToJson($data['service'] ?? null),
                'alcohol' => $data['alcohol'] ?? null,
                'bring_with_you' => $this->explodeToJson($data['bring_with_you'] ?? null),
                'payment_methods' => $this->explodeToJson($data['payment_methods'] ?? null),
                'service_fee' => !empty($data['service_fee']) ? (int)$data['service_fee'] : null,
                'description' => $data['description'] ?? null,
                'text_obj' => $data['text_obj'] ?? null,
                'created_at' => now(),
            ]);

            // === obj_features ===
            $this->repository->createObjFeatures([
                'obj_id'         => $objId,
                'banquet_note'   => $data['banquet_note'] ?? null,
                'prepayment'     => $data['prepayment'] ?? null,
                'textile_package'=> $this->explodeToJson($data['textile_package'] ?? null),
                'textile_colors' => $data['textile_colors'] ?? null,
                'tables'         => $this->explodeToJson($data['tables'] ?? null),
                'loud_music'     => $data['loud_music'] ?? null,
                'parking'        => $data['parking'] ?? null,
                'pier'           => $data['pier'] ?? null,
                'equipment'      => !empty($data['equipment']) ? json_encode($data['equipment'], JSON_UNESCAPED_UNICODE) : '[]',
                'kids'           => !empty($data['kids']) ? json_encode($data['kids'], JSON_UNESCAPED_UNICODE) : '[]',
                'interior'       => $data['interior'] ?? null,
                'location'       => $this->explodeToJson($data['location'] ?? null),
                'created_at'     => now(),
            ]);

            // === subjs (залы) ===
            $perPerson = !empty($data['per_person']) ? (int)$data['per_person'] : null;
            $siteTypeJson = $this->explodeToJson($data['site_type'] ?? null);
            $loudMusicUntil = $data['loud_music'] ?? null;
            $workingHours = $data['working_hours'] ?? null;

            foreach ($data['halls'] ?? [] as $hall) {
                $subjId = $this->repository->createSubj([
                    'obj_id' => $objId,
                    'name_subj' => $hall['name'] ?? null,
                    'minimum_cost' => !empty($hall['minimum_cost']) ? (int)$hall['minimum_cost'] : null,
                    'per_person' => $perPerson,
                    'capacity_to' => !empty($hall['capacity']) ? (int)$hall['capacity'] : null,
                    'furshet' => !empty($hall['furshet']) ? (int)$hall['furshet'] : null,
                    'site_type' => $siteTypeJson,
                    'loud_music_until' => $loudMusicUntil,
                    'rent' => $hall['rent'] ?? null,
                    'working_hours' => $workingHours,
                    'features' => $hall['gorko_features'] ?? '',
                    'published' => 0,
                    'created_at' => now(),
                ]);

                // === Загрузка фото ===
                if (!empty($hall['photos'])) {
                    $this->uploadHallPhotos($subjId, $hall['photos']);
                }
            }
        });

        if (!empty($data['actions'])) {
            $this->saveActions($objId, $data['actions']);
        }

        return $objId;
    }

    private function saveActions(int $objId, string $actionsText): void
    {
        // Удаляем старые акции для этого объекта
        Action::where('obj_id', $objId)->delete();
        Action::create([
            'obj_id' => $objId,
            'actions' => $actionsText,
        ]);

    }

    /**
     * Разбивает строку через запятую в массив (без json_encode — модель сделает это сама через $casts)
     */
    private function explodeToArray(?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        $parts = explode(',', $value);
        $parts = array_map('trim', $parts);
        return array_filter($parts, fn($v) => $v !== '');
    }


    /**
     * Скачивает фото по URL, передаёт в ImgSubjService, удаляет временный файл.
     */
    private function uploadHallPhotos(int $subjId, array $photoUrls): void
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        foreach ($photoUrls as $photoUrl) {
            $tempPath = $tempDir . '/' . uniqid('parser_') . '.jpg';

            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                            . 'AppleWebKit/537.36 (KHTML, like Gecko) '
                            . 'Chrome/120.0.0.0 Safari/537.36',
                    ])
                    ->get($photoUrl);

                if (!$response->successful()) {
                    Log::channel('error_file')->error('HTTP ошибка при скачивании фото', [
                        'url' => $photoUrl,
                        'status' => $response->status(),
                        'subj_id' => $subjId,
                    ]);
                    continue;
                }

                $photoData = $response->body();

                if (empty($photoData)) {
                    Log::channel('error_file')->error('Пустой ответ при скачивании фото', [
                        'url' => $photoUrl,
                        'subj_id' => $subjId,
                    ]);
                    continue;
                }

                file_put_contents($tempPath, $photoData);

                $uploadedFile = new UploadedFile(
                    $tempPath,
                    basename($photoUrl),
                    'image/jpeg',
                    null,
                    true
                );

                $fakeRequest = new Request();
                $fakeRequest->files->set('img', $uploadedFile);

                $result = $this->imgSubjService->ImgSubjStore($fakeRequest, $subjId);

                if ($result === null) {
                    Log::channel('error_file')->error('Сервис вернул null', [
                        'url' => $photoUrl,
                        'subj_id' => $subjId,
                    ]);
                }

                usleep(500000);

            } catch (\Exception $e) {
                Log::channel('error_file')->error('Ошибка загрузки фото', [
                    'url' => $photoUrl,
                    'subj_id' => $subjId,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
            }
        }
    }

    // ============================================================
    //  PRIVATE: HTTP
    // ============================================================

    /**
     * Загрузка HTML страницы.
     */
    private function fetchHtml(string $url): string
    {
        return Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                    . 'AppleWebKit/537.36 (KHTML, like Gecko) '
                    . 'Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
            ])
            ->get($url)
            ->body();
    }

    // ============================================================
    //  PRIVATE: Парсинг avtobanket.ru
    // ============================================================

    /**
     * Извлечение данных из HTML страницы.
     */
    private function extractData(string $html): array
    {
        $html = '<?xml encoding="UTF-8">' . $html;
        $dom = new \DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

        $xpath = new \DOMXPath($dom);

        // Название объекта
        $nameObj = '';
        $h1Nodes = $xpath->query("//h1");
        if ($h1Nodes->length > 0) {
            $nameObj = $this->cleanText($h1Nodes->item(0)->textContent);

            $cutMarkers = [
                'Проведение свадьбы', 'Проведение банкета', 'Проведение свадьбы и банкета',
                'Банкет / Свадьба', 'Банкет / Свадьба /',
                'Банкетное меню', 'от ', 'от 3', 'от 2', 'от 4', 'от 5',
            ];
            foreach ($cutMarkers as $marker) {
                $pos = mb_stripos($nameObj, $marker);
                if ($pos !== false && $pos > 0) {
                    $nameObj = trim(mb_substr($nameObj, 0, $pos));
                    break;
                }
            }
        }

        // Метки для парсинга
        $labels = [
            'banquet_menu' => 'Банкетное меню',
            'banquet_note' => 'Примечание',
            'service_fee' => 'Проценты за обслуживание',
            'own_alcohol' => 'Свой алкоголь',
            'bring_with_you' => 'Можно принести с собой',
            'prepayment' => 'Предоплата',
            'textile_package' => 'Текстильный пакет',
            'textile_colors' => 'Расцветки текстильного пакета',
            'tables' => 'Столы',
            'loud_music' => 'Громкая музыка',
            'parking' => 'Парковка',
            'pier' => 'Причал',
            'own_equipment' => 'Своё оборудование',
            'projector_screen' => 'Проекторный экран',
            'music_stage' => 'Музыкальная сцена',
            'sound_equipment' => 'Звуковое оборудование',
            'light_equipment' => 'Световое оборудование',
            'karaoke' => 'Караоке',
            'wifi' => 'Wi-Fi',
            'dance_floor' => 'Танцпол',
            'air_conditioner' => 'Кондиционер',
            'wardrobe' => 'Гардероб',
            'dressing_rooms' => 'Гримёрки для артистов',
            'kids_room' => 'Детская комната',
            'kids_menu' => 'Детское меню',
            'kids_corner' => 'Детский уголок',
            'interior' => 'Интерьер',
            'location' => 'Месторасположение',
            'working_hours' => 'Режим работы',
        ];

        $result = [
            'name_obj' => $nameObj,
            'phone_obj' => null,
        ];

        foreach ($labels as $key => $label) {
            $result[$key] = $this->findFieldValue($xpath, $dom, $label);
        }

        if (!empty($result['loud_music'])) {
            $result['loud_music'] = preg_replace('/^до\s+/iu', '', trim($result['loud_music']));
        }

        if (!empty($result['banquet_menu'])) {
            if (preg_match('/(\d[\d\s]*)/u', $result['banquet_menu'], $m)) {
                $result['banquet_menu'] = preg_replace('/\s+/u', '', $m[1]);
            } else {
                $result['banquet_menu'] = null;
            }
        }


        $result['tables'] = $this->capitalizeCommaString($result['tables'] ?? null);
        $result['bring_with_you'] = $this->capitalizeCommaString($result['bring_with_you'] ?? null);
        $this->normalizeIntFields($result);
        $result['alcohol'] = $this->normalizeAlcohol($result['own_alcohol'] ?? null);
        $result['actions'] = $this->extractActions($xpath, $dom);
        $result['_halls'] = $this->extractHalls($xpath, $dom);
        $result['text_obj'] = $this->extractTextObj($xpath, $dom);
        $result['description'] = $result['actions'] ?? '';

        return $result;
    }

    /**
     * Разбивает строку по запятой, делает каждое значение с заглавной буквы,
     * склеивает обратно в строку.
     */
    private function capitalizeCommaString(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        $parts = array_map('trim', explode(',', $value));
        $parts = array_filter($parts, fn($v) => $v !== '');

        $parts = array_map(function ($item) {
            if (!is_string($item) || $item === '') {
                return $item;
            }
            return mb_strtoupper(mb_substr($item, 0, 1)) . mb_substr($item, 1);
        }, $parts);

        return implode(', ', $parts);
    }


    /**
     * Специальное извлечение блока «Акции, скидки и подарки».
     */
    private function extractActions(\DOMXPath $xpath, \DOMDocument $dom): ?string
    {
        $fullText = $this->cleanText($dom->textContent);

        $start = mb_strpos($fullText, 'Акции, скидки и подарки');
        if ($start === false) {
            return null;
        }

        $endMarkers = [
            'Дополнительная информация', 'Текстильный пакет',
            'Оборудование', 'Особенности заведения',
            'Стоимость и условия',
        ];
        $end = mb_strlen($fullText);
        foreach ($endMarkers as $marker) {
            $pos = mb_strpos($fullText, $marker, $start + 10);
            if ($pos !== false && $pos < $end) {
                $end = $pos;
            }
        }

        $actionsText = mb_substr($fullText, $start, $end - $start);

        if (preg_match('/Банкет\s/u', $actionsText, $m, PREG_OFFSET_CAPTURE)) {
            $actionsText = substr($actionsText, $m[0][1]);
        } else {
            $actionsText = preg_replace('/^Акции, скидки и подарки[^\n]*/u', '', $actionsText);
        }

        $actionsText = preg_replace('/Банкет\s*/u', "\n", $actionsText);
        $actionsText = preg_replace('/[^\S\n]+/u', ' ', $actionsText);
        $actionsText = preg_replace('/\n[ \t]+/u', "\n", $actionsText);
        $actionsText = trim($actionsText);

        return $actionsText !== '' ? $actionsText : null;
    }

    /**
     * Извлечение описания объекта (text_obj).
     */
    private function extractTextObj(\DOMXPath $xpath, \DOMDocument $dom): ?string
    {
        $fullText = $this->cleanText($dom->textContent);

        $end = mb_strpos($fullText, 'Показать полностью');
        if ($end === false) {
            $end = mb_strpos($fullText, 'Информация по залам');
        }
        if ($end === false) {
            return null;
        }

        $beforeEnd = mb_substr($fullText, 0, $end);
        $beforeEnd = preg_replace('/\.[a-zA-Z_][a-zA-Z0-9_-]*\s*\{[^}]*\}\s*/u', '', $beforeEnd);

        // Стратегия 1: счётчик фото есть в тексте
        if (preg_match_all('/\d+\/\d+\.\s*Зал\s+\d+\s+из\s+\d+\s*:\s*/u', $beforeEnd, $matches, PREG_OFFSET_CAPTURE)) {
            $lastMatch = end($matches[0]);
            $startByte = $lastMatch[1] + strlen($lastMatch[0]);
            $text = trim(substr($beforeEnd, $startByte));

            if (preg_match('/\d+\/\d+\.\s*Зал\s+\d+\s+из\s+\d+\s*:\s*(.+?)\s+\d+\/\d+\.\s*Зал\s+\d+\s+из\s+\d+\s*:\s*/u', $beforeEnd, $hm)) {
                $hallName = trim($hm[1]);
                if ($hallName !== '' && mb_strpos(mb_strtolower($text), mb_strtolower($hallName)) === 0) {
                    $text = trim(mb_substr($text, mb_strlen($hallName)));
                }
            }

            if ($text !== '' && mb_strlen($text) > 50) {
                return $text;
            }
        }

        // Стратегия 2: счётчика нет — ищем текст после навигационных вкладок
        if (preg_match('/(?:Меню|Дополнительно|Скидки и подарки)\s+\d+\s+(.+)/u', $beforeEnd, $m)) {
            $text = trim($m[1]);
            $text = preg_replace('/\d+\/\d+\.\s*Зал\s+\d+\s+из\s+\d+\s*:\s*/u', '', $text);
            $text = preg_replace('/^[А-Яа-яЁё\s]{5,60}(?=Представляем|Зеркала|Ресторан|Зал|Наш|Добро|Приглашаем)/u', '', $text);
            $text = trim($text);
            if ($text !== '' && mb_strlen($text) > 50) {
                return $text;
            }
        }

        // Стратегия 3: XPath — ищем блок с описанием
        $descNodes = $xpath->query(
            "//div[contains(@class, 'description')] | //div[contains(@class, 'desc')] | //p[contains(@class, 'desc')]"
        );
        foreach ($descNodes as $node) {
            $text = $this->cleanText($node->textContent);
            if (mb_strlen($text) > 100) {
                return $text;
            }
        }

        return null;
    }

    /**
     * Извлечение данных по залам: фото (до 10), Вместимость, Аренда.
     */
    private function extractHalls(\DOMXPath $xpath, \DOMDocument $dom): array
    {
        $photosByHall = [];
        $hallNames = [];

        // 1. Соответствие хэш -> оригинал из JSON-LD
        $hashToOrig = [];
        $jsonLdNodes = $xpath->query("//script[@type='application/ld+json']");

        foreach ($jsonLdNodes as $node) {
            $json = json_decode($node->textContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) continue;

            $walker = function ($data) use (&$walker, &$hashToOrig) {
                if (is_array($data)) {
                    if (isset($data['@type']) && $data['@type'] === 'ImageObject') {
                        $thumb = $data['thumbnailUrl'] ?? '';
                        $orig = $data['contentUrl'] ?? '';
                        if (preg_match('/_crop_([a-f0-9]{32})\.jpg$/i', $thumb, $m)) {
                            $hashToOrig[$m[1]] = $orig;
                        }
                    }
                    foreach ($data as $value) {
                        $walker($value);
                    }
                }
            };
            $walker($json);
        }

        // 2. Превью с alt — извлекаем зал и хэш
        $imgs = $xpath->query("//img[contains(@alt, 'фото')]");

        foreach ($imgs as $img) {
            $alt = $img->getAttribute('alt');
            $src = $img->getAttribute('src');
            if (!$alt || !$src) continue;

            if (!preg_match('/\.\s*(.+?),\s*фото\s*\d+/u', $alt, $m)) continue;
            $hallName = trim($m[1]);

            if (!preg_match('/_crop_([a-f0-9]{32})\.jpg$/i', $src, $m2)) continue;
            $hash = $m2[1];

            if (isset($hashToOrig[$hash])) {
                $bigUrl = $hashToOrig[$hash];
            } else {
                if (preg_match('#/upload/image_gallery/(\d+)/#', $src, $m3)) {
                    $dir = $m3[1];
                    $bigUrl = "https://www.avtobanket.ru/upload/image_gallery_orig/{$dir}/{$hash}.jpg";
                } else {
                    continue;
                }
            }

            if (!isset($photosByHall[$hallName])) {
                $photosByHall[$hallName] = [];
                $hallNames[] = $hallName;
            }
            if (count($photosByHall[$hallName]) < 10) {
                $photosByHall[$hallName][] = $bigUrl;
            }
        }

        // 3. Текст про залы (Вместимость, Аренда)
        $fullText = $this->cleanText($dom->textContent);

        $start = mb_strpos($fullText, 'Информация по залам');
        if ($start === false) {
            return $this->buildHallsArray($hallNames, $photosByHall, [], []);
        }

        $endMarkers = ['Условия банкета', 'Банкетное меню', 'Альбомы'];
        $end = mb_strlen($fullText);
        foreach ($endMarkers as $marker) {
            $pos = mb_strpos($fullText, $marker, $start + 10);
            if ($pos !== false && $pos < $end) {
                $end = $pos;
            }
        }

        $hallsText = mb_substr($fullText, $start, $end - $start);
        $hallsText = preg_replace('/[^.]*фото\s*\d+/u', '', $hallsText);
        $hallsText = $this->cleanText($hallsText);

        preg_match_all('/Вместимость\s*:\s*(.+?)(?=\s*Депозит)/u', $hallsText, $capacityMatches);
        preg_match_all('/Аренда\s*:\s*(.+?)(?=\s*(?:Примечание|Зал работает|Условия|Альбомы|Информация|Банкетное|$))/u', $hallsText, $rentMatches);

        return $this->buildHallsArray(
            $hallNames,
            $photosByHall,
            $capacityMatches[1] ?? [],
            $rentMatches[1] ?? []
        );
    }

    /**
     * Сборка финального массива залов.
     */
    private function buildHallsArray(array $hallNames, array $photosByHall, array $capacities, array $rents): array
    {
        $maxHalls = max(count($hallNames), count($capacities), count($rents));

        if ($maxHalls === 0) {
            return [];
        }

        $halls = [];
        for ($i = 0; $i < $maxHalls; $i++) {
            $hallName = $hallNames[$i] ?? ('Зал ' . ($i + 1));
            $halls[] = [
                'name' => $hallName,
                'capacity' => $this->normalizeCapacity($capacities[$i] ?? null),
                'rent' => $rents[$i] ?? null,
                'photos' => $photosByHall[$hallName] ?? [],
            ];
        }

        return $halls;
    }

    /**
     * Поиск значения поля по текстовой метке.
     */
    private function findFieldValue(\DOMXPath $xpath, \DOMDocument $dom, string $label): ?string
    {
        $stopMarkers = [
            'Банкетное меню', 'Примечание', 'Проценты за обслуживание',
            'Свой алкоголь', 'Можно принести с собой', 'Предоплата',
            'Акции, скидки и подарки',
            'Текстильный пакет', 'Расцветки текстильного пакета', 'Столы',
            'Громкая музыка', 'Парковка', 'Причал', 'Ближайший ЗАГС',
            'Своё оборудование', 'Проекторный экран', 'Музыкальная сцена',
            'Звуковое оборудование', 'Световое оборудование', 'Система затемнения',
            'Караоке', 'Wi-Fi', 'Танцпол', 'Кондиционер', 'Гардероб',
            'Гримёрки для артистов', 'Детская комната',
            'Детское меню', 'Детский уголок',
            'Интерьер', 'Месторасположение', 'Режим работы', 'Сайт',
            'Условия банкета', 'Дополнительная информация', 'Оборудование',
            'Особенности заведения',
            'Стоимость и условия', 'Не является публичной офертой',
            'Отзывы', 'Рестораны, расположенные неподалеку',
            'Как с нами сотрудничать', 'Статьи', 'Информация об оферте',
            'Оставаясь на сайте', 'Заголовок',
        ];

        // Способ 1: <strong> или <b>
        $nodes = $xpath->query(
            sprintf("//strong[contains(text(), '%s')] | //b[contains(text(), '%s')]", $label, $label)
        );

        if ($nodes->length > 0) {
            $node = $nodes->item(0);
            $parent = $node->parentNode;
            if ($parent) {
                $text = $this->cleanText($parent->textContent);
                $text = preg_replace('/^' . preg_quote($label, '/') . '\s*:?\s*/u', '', $text);
                $text = $this->trimByStopMarkers($text, $stopMarkers);
                if ($text !== '') {
                    return $text;
                }
            }
        }

        // Способ 2: любой узел, текст которого начинается с метки
        $nodes = $xpath->query(sprintf("//*[contains(text(), '%s')]", $label));

        foreach ($nodes as $node) {
            $text = $this->cleanText($node->textContent);
            if (mb_strpos($text, $label) === 0) {
                $text = preg_replace('/^' . preg_quote($label, '/') . '\s*:?\s*/u', '', $text);
                $text = $this->trimByStopMarkers($text, $stopMarkers);
                if ($text !== '') {
                    return $text;
                }
            }
        }

        // Способ 3: regex по полному тексту
        $fullText = $this->cleanText($dom->textContent);
        $pattern = '/' . preg_quote($label, '/') . '\s*:\s*([^\n\r]{2,500})/iu';
        if (preg_match($pattern, $fullText, $matches)) {
            $value = $this->trimByStopMarkers(trim($matches[1]), $stopMarkers);
            return $value !== '' ? $value : null;
        }

        return null;
    }

    // ============================================================
    //  PRIVATE: Парсинг gorko.ru
    // ============================================================

    /**
     * Загрузка и парсинг данных с gorko.ru.
     */
    private function fetchGorkoData(string $url): array
    {
        $debug = [];

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
            return ['_halls' => [], '_debug' => ['Ошибка gorko: ' . $e->getMessage()]];
        }

        // Парсим __initialData__
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
            return ['_halls' => [], '_debug' => ['__initialData__ не найден']];
        }

        $debug[] = '__initialData__ найден';

        // venueId и entityData
        $venueId = null;
        $entityData = null;
        if (!empty($initialData['ProfileVenue']['loadEntity']['data'])) {
            $entityData = $initialData['ProfileVenue']['loadEntity']['data'];
            $venueId = array_key_first($entityData);
        }

        if (!$venueId) {
            return ['_halls' => [], '_debug' => ['venueId не найден']];
        }

        $entity = $entityData[$venueId] ?? [];
        $debug[] = "Entity keys: " . implode(', ', array_keys($entity));

        // === Сборщик характеристик ===
        $featuresLookup = [];

        $collectFeatures = function ($data) use (&$collectFeatures, &$featuresLookup) {
            if (!is_array($data)) return;

            if (isset($data['name']) && isset($data['text']) && is_string($data['name']) && is_string($data['text'])) {
                $text = $data['text'];
                $text = preg_replace('/<br\s*\/?>/iu', "\n", $text);
                $text = preg_replace('/<\/(p|li|ul|ol|div)>/iu', "\n", $text);
                $text = preg_replace('/<\/?[^>]+>/iu', '', $text);
                $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = preg_replace('/[^\S\n]+/u', ' ', $text);
                $text = preg_replace('/\n[ \t]*\n/u', "\n", $text);
                $text = preg_replace('/\n{2,}/u', "\n", $text);
                $text = trim($text);

                $featuresLookup[$data['name']] = $text;
            }

            foreach ($data as $value) {
                if (is_array($value)) {
                    $collectFeatures($value);
                }
            }
        };

        $collectFeatures($entity);

        $debug[] = "Найдено характеристик (name→text): " . count($featuresLookup);
        foreach ($featuresLookup as $name => $text) {
            $debug[] = "  «{$name}» = «" . mb_substr($text, 0, 120) . "»";
        }

        // === Подготовка текста страницы для текстовых фолбэков ===
        $htmlForText = preg_replace('/<\/(div|p|li|span|a|button|section|article|label|h[1-6])>/i', ' ', $html);
        $htmlForText = preg_replace('/<(br|hr)[^>]*>/i', ' ', $htmlForText);
        $htmlForText = '<?xml encoding="UTF-8">' . $htmlForText;
        $domGorko = new \DOMDocument();
        @$domGorko->loadHTML($htmlForText, LIBXML_NOERROR | LIBXML_NOWARNING);
        $fullGorkoText = $this->cleanText($domGorko->textContent);

        // === Извлечение общих полей ===
        $fieldAliases = [
            'site_type' => ['Тип площадки', 'Тип заведения', 'Тип'],
            'kitchen' => ['Кухня', 'Кухни', 'Кухня ресторана'],
            'gorko_features' => ['Особенности', 'Особенности заведения', 'Особенности площадки'],
            'service' => ['Сервисы за отдельную плату', 'Сервисы', 'Платные сервисы', 'Дополнительно'],
            'for_events' => ['Подходит для', 'Подходит для мероприятий', 'Тип мероприятия', 'Мероприятия'],
            'payment_methods' => ['Способы оплаты', 'Оплата', 'Способ оплаты'],
            'description' => ['Описание', 'Описание заведения', 'О ресторане'],
        ];

        $common = [
            'for_events' => '',
            'gorko_features' => '',
            'service' => '',
            'site_type' => '',
            'kitchen' => '',
            'payment_methods' => '',
            'description' => '',
        ];

        foreach ($fieldAliases as $field => $names) {
            foreach ($names as $name) {
                if (!empty($featuresLookup[$name])) {
                    $common[$field] = $featuresLookup[$name];
                    $debug[] = "Извлечено: {$field} = «{$name}» → " . mb_substr($featuresLookup[$name], 0, 100);
                    break;
                }
            }
        }

        if (!empty($common['kitchen'])) {
            $kitchens = array_map('trim', explode(',', $common['kitchen']));
            $kitchens = array_map(fn($k) => mb_strtoupper(mb_substr($k, 0, 1)) . mb_substr($k, 1), $kitchens);
            $common['kitchen'] = implode(', ', $kitchens);
        }

        // === Текстовый fallback для "Способы оплаты" ===
        if (empty($common['payment_methods'])) {
            $posPay = mb_strpos($fullGorkoText, 'Способы оплаты');
            if ($posPay !== false) {
                $afterPay = mb_substr($fullGorkoText, $posPay + mb_strlen('Способы оплаты'));
                $afterPay = preg_replace('/^[\s:]+/u', '', $afterPay);

                $stopPay = [
                    'При организации банкета', 'Дополнительные услуги', 'Банкетное меню',
                    'Кондитерское меню', 'Помещения', 'Описание', 'Тип площадки',
                    'Кухня', 'Особенности', 'Подходит для', 'Сервисы',
                ];

                $cutPay = mb_strlen($afterPay);
                foreach ($stopPay as $sw) {
                    $p = mb_strpos($afterPay, $sw);
                    if ($p !== false && $p < $cutPay) {
                        $cutPay = $p;
                    }
                }

                $payText = trim(mb_substr($afterPay, 0, $cutPay));
                if ($payText !== '' && mb_strlen($payText) > 2) {
                    $common['payment_methods'] = $payText;
                    $debug[] = "Текстовый fallback: payment_methods = " . mb_substr($payText, 0, 200);
                }
            }
        }

        // === Текстовый fallback для "Подходит для мероприятий" ===
        if (empty($common['for_events'])) {
            $pos = mb_strpos($fullGorkoText, 'Подходит для мероприятий');
            if ($pos !== false) {
                $after = mb_substr($fullGorkoText, $pos + mb_strlen('Подходит для мероприятий'));
                $after = preg_replace('/^[\s:]+/u', '', $after);

                $stopEvents = [
                    'При организации банкета', 'Дополнительные услуги', 'Банкетное меню',
                    'Кондитерское меню', 'Каталог дополнительных', 'Развернуть',
                    'Помещения', 'Белый зал', 'Зеркальный зал', 'Сиреневый зал',
                    'Каминный зал', 'Зал кафе', 'Описание', 'Тип площадки',
                ];

                $cutAt = mb_strlen($after);
                foreach ($stopEvents as $sw) {
                    $p = mb_strpos($after, $sw);
                    if ($p !== false && $p < $cutAt) {
                        $cutAt = $p;
                    }
                }

                $eventsText = trim(mb_substr($after, 0, $cutAt));
                if ($eventsText !== '' && mb_strlen($eventsText) > 2) {
                    $pattern = '/(Корпоратив на Новый Год|Свадьба|Корпоратив|День рождения|Выпускной|Юбилей|Банкет|Фуршет|Конференция|Семинар|Презентация|Cocktail party|Новый год|8 марта|23 февраля|Halloween|День святого Валентина)/iu';
                    preg_match_all($pattern, $eventsText, $eventMatches);
                    if (!empty($eventMatches[0])) {
                        $common['for_events'] = implode(', ', array_unique($eventMatches[0]));
                    } else {
                        $common['for_events'] = $eventsText;
                    }
                    $debug[] = "Текстовый fallback: for_events = " . mb_substr($eventsText, 0, 200);
                }
            }
        }

        // === Залы ===
        $scheduleRooms = $initialData['ProfileVenue']['loadSchedule']['data']['rooms'] ?? [];
        $entityRooms = $entity['room'] ?? [];

        $halls = [];

        foreach ($scheduleRooms as $scheduleRoom) {
            $hallId = $scheduleRoom['id'] ?? null;
            $hallName = trim($scheduleRoom['name'] ?? '');
            if (!$hallId || !$hallName) continue;

            $entityRoom = null;
            foreach ($entityRooms as $er) {
                if (($er['id'] ?? null) == $hallId) {
                    $entityRoom = $er;
                    break;
                }
            }

            $capacity = '';
            $furshet = 0;
            $minCost = 0;
            $hallFeatures = '';

            if ($entityRoom) {
                $hallLookup = [];
                $collectHallFeatures = function ($data) use (&$collectHallFeatures, &$hallLookup) {
                    if (!is_array($data)) return;
                    if (isset($data['name']) && isset($data['text']) && is_string($data['name']) && is_string($data['text'])) {
                        $text = $data['text'];
                        $text = preg_replace('/<br\s*\/?>/iu', "\n", $text);
                        $text = preg_replace('/<\/(p|li|ul|ol|div)>/iu', "\n", $text);
                        $text = preg_replace('/<\/?[^>]+>/iu', '', $text);
                        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $text = preg_replace('/[^\S\n]+/u', ' ', $text);
                        $text = preg_replace('/\n[ \t]*\n/u', "\n", $text);
                        $text = preg_replace('/\n{2,}/u', "\n", $text);
                        $hallLookup[$data['name']] = trim($text);
                    }
                    foreach ($data as $value) {
                        if (is_array($value)) {
                            $collectHallFeatures($value);
                        }
                    }
                };

                $collectHallFeatures($entityRoom);
                $collectHallFeatures($scheduleRoom);
                foreach ($entityRoom['param'] ?? [] as $param) {
                    $key = $param['key'] ?? '';
                    $value = $param['value'] ?? '';
                    $text = $param['text'] ?? '';

                    if ($key === 'param_capacity_reception') {
                        $furshet = (int)$value;
                    }
                    if ($key === 'capacity') {
                        $capacity = $this->normalizeCapacity($text);
                    }
                }

                $minCostNames = [
                    'Минимальная стоимость банкета',
                    'Минимальная стоимость',
                    'Минимальная стоимость мероприятия',
                    'Минимальная стоимость закрытия зала',
                ];
                foreach ($minCostNames as $mcName) {
                    if (!empty($hallLookup[$mcName])) {
                        $minCost = (int)preg_replace('/\D/', '', $hallLookup[$mcName]);
                        break;
                    }
                }
                if ($minCost === 0) {
                    foreach ($entityRoom['param'] ?? [] as $param) {
                        $key = $param['key'] ?? '';
                        if (in_array($key, ['param_banquet_min_price', 'min_price', 'minimum_price', 'banquet_min_cost'], true)) {
                            $minCost = (int)preg_replace('/\D/', '', $param['value'] ?? '');
                            break;
                        }
                    }
                }

                if ($minCost === 0) {
                    foreach ($scheduleRoom['param'] ?? [] as $param) {
                        $key = $param['key'] ?? '';
                        $text = $param['text'] ?? '';
                        if (in_array($key, ['param_banquet_min_price', 'min_price', 'minimum_price', 'banquet_min_cost'], true)) {
                            $minCost = (int)preg_replace('/\D/', '', $param['value'] ?? $text);
                            break;
                        }
                    }
                }

                if ($capacity === '') {
                    $capNames = ['Вместимость', 'Вместимость банкета'];
                    foreach ($capNames as $cn) {
                        if (!empty($hallLookup[$cn])) {
                            $capacity = $this->normalizeCapacity($hallLookup[$cn]);
                            break;
                        }
                    }
                }

                if ($furshet === 0) {
                    $furshetNames = ['Вместимость на фуршет', 'Фуршет'];
                    foreach ($furshetNames as $fn) {
                        if (!empty($hallLookup[$fn])) {
                            $furshet = (int)preg_replace('/\D/', '', $hallLookup[$fn]);
                            break;
                        }
                    }
                }

                $hallFeatureNames = [
                    'Особенности',
                    'Особенности зала',
                    'Особенности помещения',
                    'Особенности пространства',
                ];
                foreach ($hallFeatureNames as $fn) {
                    if (!empty($hallLookup[$fn])) {
                        $hallFeatures = $hallLookup[$fn];
                        break;
                    }
                }
            }

            $halls[] = [
                'name' => $hallName,
                'capacity_to' => $capacity,
                'furshet' => $furshet,
                'minimum_cost' => $minCost,
                'gorko_features' => $hallFeatures,
            ];

            $debug[] = "Зал: {$hallName} | cap={$capacity} | furshet={$furshet} | minCost={$minCost} | features=" . ($hallFeatures ? 'есть' : 'нет');
        }

        if (!empty($halls)) {
            $halls = $this->fillHallsFromText($halls, $fullGorkoText, $debug);
        }

        return array_merge($common, [
            '_halls' => $halls,
            '_debug' => $debug,
        ]);
    }

    /**
     * Текстовый fallback: дополняет залы минимальной стоимостью и особенностями
     * из полного текста страницы gorko.
     */
    private function fillHallsFromText(array $halls, string $fullText, array &$debug): array
    {
        foreach ($halls as &$hall) {
            $hallName = $hall['name'] ?? '';
            if (!$hallName) continue;

            // Ищем позицию зала в тексте (символьный offset)
            $hallStart = mb_strpos($fullText, $hallName);
            if ($hallStart === false) {
                $debug[] = "Text fallback: зал «{$hallName}» не найден в тексте";
                continue;
            }

            // Ищем начало следующего зала
            $nextHallPos = mb_strlen($fullText);
            $afterHall = mb_substr($fullText, $hallStart + mb_strlen($hallName));
            if (preg_match('/Зал\s+«[^»]+»/u', $afterHall, $nm)) {
                $nextHallPos = $hallStart + mb_strlen($hallName) + mb_strpos($afterHall, $nm[0]);
            }
            // Ограничиваем секцией "Описание"
            $descPos = mb_strpos($fullText, 'Описание', $hallStart + 10);
            if ($descPos !== false && $descPos < $nextHallPos) {
                $nextHallPos = $descPos;
            }

            $hallSection = mb_substr($fullText, $hallStart, $nextHallPos - $hallStart);
            $debug[] = "Text fallback: секция зала «{$hallName}» = " . mb_substr($hallSection, 0, 200);

            // Минимальная стоимость банкета
            if (empty($hall['minimum_cost'])) {
                if (preg_match('/Минимальная\s+стоимость\s+(?:банкета\s+|мероприятия\s+|закрытия\s+зала\s+)?[:\s]*(\d[\d\s]*)\s*₽?/iu', $hallSection, $mc)) {
                    $hall['minimum_cost'] = (int)preg_replace('/\D/', '', $mc[1]);
                    $debug[] = "  → minimum_cost = {$hall['minimum_cost']}";
                }
            }

            // Особенности
            if (empty($hall['gorko_features'])) {
                $featPos = mb_strpos($hallSection, 'Особенности');
                if ($featPos !== false) {
                    $featText = trim(mb_substr($hallSection, $featPos + mb_strlen('Особенности')));
                    $featText = preg_replace('/^[\s:]+/u', '', $featText);

                    // Обрезаем по маркерам конца
                    $stopMarkers = ['Зал ', 'Описание', 'Тип площадки', 'Кухня', 'Отзывы', 'Альбомы', 'Минимальная стоимость'];
                    foreach ($stopMarkers as $sm) {
                        $sp = mb_strpos($featText, $sm);
                        if ($sp !== false && $sp > 10) {
                            $featText = trim(mb_substr($featText, 0, $sp));
                        }
                    }

                    if ($featText !== '' && mb_strlen($featText) > 5) {
                        $hall['gorko_features'] = $featText;
                        $debug[] = "  → gorko_features = " . mb_substr($featText, 0, 200);
                    }
                }
            }
        }
        unset($hall);

        return $halls;
    }

    /**
     * Слияние данных gorko.ru в данные avtobanket.ru.
     */
    private function mergeGorkoData(array $abData, array $gorkoData): array
    {
        foreach (['for_events', 'gorko_features', 'service', 'site_type', 'kitchen', 'payment_methods', 'description'] as $field) {
            if (!empty($gorkoData[$field])) {
                $abData[$field] = $gorkoData[$field];
            } else {
                $abData[$field] = $abData[$field] ?? '';
            }
        }

        // Склейка description (gorko "Описание") + actions (avtobanket)
        $gorkoDesc = $gorkoData['description'] ?? '';
        $abActions = $abData['actions'] ?? '';
        if ($gorkoDesc && $abActions) {
            $abData['description'] = $gorkoDesc . "\n\n" . $abActions;
        } elseif ($gorkoDesc) {
            $abData['description'] = $gorkoDesc;
        } elseif ($abActions) {
            $abData['description'] = $abActions;
        } else {
            $abData['description'] = '';
        }

        $mergeDebug = [];
        $mergeDebug[] = "AB залов: " . count($abData['_halls']);
        $mergeDebug[] = "Gorko залов: " . count($gorkoData['_halls'] ?? []);

        $gorkoHalls = $gorkoData['_halls'] ?? [];

        foreach ($abData['_halls'] as $i => &$abHall) {
            $abName = $this->normalizeHallName($abHall['name']);
            $matched = false;

            foreach ($gorkoHalls as $gHall) {
                $gName = $this->normalizeHallName($gHall['name']);
                $mergeDebug[] = "Сравнение: AB «{$abHall['name']}» vs Gorko «{$gHall['name']}» → " . ($gName === $abName ? 'СОВПАДЕНИЕ' : 'нет');

                if ($gName === $abName) {
                    $abHall['capacity_to'] = $gHall['capacity_to'] ?? '';
                    $abHall['furshet'] = $gHall['furshet'] ?? 0;
                    $abHall['minimum_cost'] = $gHall['minimum_cost'] ?? 0;
                    $abHall['gorko_features'] = $gHall['gorko_features'] ?? '';
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $abHall['capacity_to'] = '';
                $abHall['furshet'] = 0;
                $abHall['minimum_cost'] = 0;
                $abHall['gorko_features'] = '';
                $mergeDebug[] = "  → Зал «{$abHall['name']}» НЕ сопоставлен";
            }
        }
        unset($abHall);

        $abData['_gorko_debug'] = array_merge($gorkoData['_debug'] ?? [], ['--- MERGE ---'], $mergeDebug);

        return $abData;
    }


    private function normalizeHallName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        // Убираем слово "зал" в начале
        $name = preg_replace('/^зал\s*/u', '', $name);
        // Убираем кавычки
        $name = str_replace(['«', '»', '"', '"', "'", "'"], '', $name);
        // Убираем лишние пробелы
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        return $name;
    }

    private function explodeToJson(?string $val): ?string
    {
        if (empty($val)) return null;
        return json_encode(array_map('trim', explode(',', $val)));
    }

    private function resolveUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (str_starts_with($url, '/')) {
            return 'https://www.avtobanket.ru' . $url;
        }
        return $url;
    }

    private function normalizeAlcohol(?string $value): ?string
    {
        if (empty($value)) return null;

        $lower = mb_strtolower($value);

        if (mb_strpos($lower, 'бесплат') !== false) return '0';
        if (mb_strpos($lower, 'запрещ') !== false || mb_strpos($lower, 'нельзя') !== false) return '1';

        if (mb_strpos($lower, 'плат') !== false || mb_strpos($lower, 'сбор') !== false || preg_match('/\d/', $value)) {
            $price = '0';
            if (preg_match('/(\d[\d\s]*)/u', $value, $m)) {
                $price = preg_replace('/\s+/u', '', $m[1]);
            }
            return '2:' . $price;
        }

        return '0';
    }

    private function normalizeIntFields(array &$result): void
    {
        $intFields = ['banquet_menu', 'service_fee'];

        foreach ($intFields as $field) {
            if (empty($result[$field])) {
                $result[$field] = null;
                continue;
            }
            if (preg_match('/(\d[\d\s]*)/u', $result[$field], $m)) {
                $result[$field] = preg_replace('/\s+/u', '', $m[1]);
            } else {
                $result[$field] = null;
            }
        }
    }

    private function normalizeCapacity(?string $value): ?string
    {
        if (empty($value)) return null;

        if (preg_match_all('/\d[\d\s]*/u', $value, $m)) {
            $numbers = array_map(fn($n) => (int)preg_replace('/\s+/', '', $n), $m[0]);
            return (string)max($numbers);
        }

        return null;
    }

    private function trimByStopMarkers(string $value, array $stopMarkers): string
    {
        foreach ($stopMarkers as $marker) {
            $pos = mb_strpos($value, $marker);
            if ($pos !== false && $pos > 0) {
                $value = trim(mb_substr($value, 0, $pos));
            }
        }

        $bracePos = mb_strpos($value, '{');
        if ($bracePos !== false && $bracePos > 0) {
            $value = trim(mb_substr($value, 0, $bracePos));
        }

        return $value;
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }
}
