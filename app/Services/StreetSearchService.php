<?php

namespace App\Services;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class StreetSearchService extends Service
{
    /**
     * Основной метод поиска улиц
     */
    public function searchStreets(string $city, string $query): JsonResponse
    {
        try {
            Log::info('Starting street search', [
                'city' => $city,
                'query' => $query
            ]);

            $response = $this->makeApiRequest($city, $query);
            $data = $response->json();

            if (!$response->successful()) {
                return $this->handleApiError($response->status(), $data);
            }

            $formattedResponse = $this->formatStreetsResponse($data, $query);

            Log::info('Street search completed successfully', [
                'city' => $city,
                'query' => $query,
                'result_count' => count($formattedResponse->getData(true)['streets'] ?? [])
            ]);

            return $formattedResponse;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error to Nominatim for street search', [
                'city' => $city,
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'connection_error',
                'message' => 'Проблема с подключением к сервису геоданных. Проверьте интернет-соединение.',
                'details' => [
                    'service' => 'Nominatim',
                    'city' => $city,
                    'query' => $query
                ]
            ], 503);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::warning('HTTP request error for street search', [
                'city' => $city,
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'response_body' => $e->response->body() ?? 'no response',
                'status_code' => $e->response->status() ?? 'unknown'
            ]);
            return response()->json([
                'error' => 'http_request_error',
                'message' => 'Ошибка при запросе к сервису геоданных.',
                'details' => [
                    'status_code' => $e->response->status() ?? 'unknown',
                    'response' => $e->response->body()
                ]
            ], $e->response->status() ?? 500);

        } catch (TransferException $e) {
            Log::error('Transfer error for street search', [
                'city' => $city,
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'transfer_error',
                'message' => 'Ошибка передачи данных. Попробуйте позже.',
                'details' => [
                    'city' => $city,
                    'query' => $query
                ]
            ], 504);

        } catch (\JsonException $e) {
            Log::error('JSON parsing error in street search', [
                'city' => $city,
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'json_parse_error',
                'message' => 'Ошибка обработки данных от сервиса.',
                'details' => [
                    'city' => $city,
                    'query' => $query
                ]
            ], 502);

        } catch (\Exception $e) {
            Log::critical('Unexpected error in street search service', [
                'city' => $city,
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'correlation_id' => $correlationId = (string) Str::uuid()
            ]);
            return response()->json([
                'error' => 'internal_error',
                'message' => 'Произошла внутренняя ошибка. Попробуйте позже или обратитесь к администратору.',
                'correlation_id' => $correlationId,
                'details' => [
                    'city' => $city,
                    'query' => $query
                ]
            ], 500);
        }
    }

    /**
     * Выполняет запрос к Nominatim API
     */
    private function makeApiRequest(string $city, string $query): Response
    {
        $url = 'https://nominatim.openstreetmap.org/search';
        $params = [
            'q' => "{$query}, {$city}",
            'countrycodes' => 'RU',
            'format' => 'json',
            'addressdetails' => '1',
            'limit' => 5,
            'featuretype' => 'street'
        ];

        $client = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'MyCitySearchApp/1.0 (0120912@mail.ru)',
                'Accept' => 'application/json'
            ]);

        // Отключаем проверку SSL только в тестовой среде
        if (app()->environment('testing')) {
            $client->withOptions(['verify' => false]);
        }

        return $client->get($url, $params);
    }

    /**
     * Обрабатывает ошибки API
     */
    private function handleApiError(int $statusCode): JsonResponse
    {
        Log::error('Nominatim API error: ' . $statusCode);

        switch ($statusCode) {
            case 429:
                return response()->json(['error' => 'Превышен лимит запросов к API'], 429);
            case $statusCode >= 500:
                return response()->json(['error' => 'API временно недоступно'], 503);
            default:
                return response()->json(['error' => 'Ошибка API'], $statusCode);
        }
    }

    /**
     * Форматирует данные улиц для ответа
     */
    private function formatStreetsResponse($data, string $query): JsonResponse
    {
        if (!is_array($data)) {
            Log::warning('API returned non‑array data, returning empty array', [
                'data_type' => gettype($data),
                'data' => $data
            ]);
            return response()->json([]);
        }

        if (empty($data)) {
            Log::info('API returned empty array, returning empty JSON response');
            return response()->json([]);
        }

        $streets = [];
        $streetNames = []; // Треккер уже добавленных названий улиц
        $queryLower = mb_strtolower($query, 'UTF-8'); // Сохраняем нижний регистр запроса

        foreach ($data as $place) {
            if (!is_array($place)) {
                continue; // Пропускаем некорректные записи
            }

            $streetName = $this->extractStreetName($place);
            if (!$streetName) {
                continue; // Пропускаем, если название улицы не найдено
            }

            $streetNameLower = mb_strtolower($streetName, 'UTF-8');

            // Фильтруем по точному совпадению с учётом суффиксов
            if ($this->isRelevantStreet($streetNameLower, $queryLower)) {
                // Проверяем, не добавлен ли уже такой адрес в список
                if (!in_array($streetName, $streetNames)) {
                    $streets[] = [
                        'name' => $streetName,
                        'lat' => $place['lat'] ?? null,
                        'lon' => $place['lon'] ?? null
                    ];
                    $streetNames[] = $streetName; // Добавляем название в треккер
                }
            }
        }

        return response()->json($streets);
    }

    /**
     * Извлекает название улицы из данных API
     */
    private function extractStreetName(array $place): ?string
    {
        if (isset($place['address']['road'])) {
            return $place['address']['road'];
        }
        if (isset($place['display_name'])) {
            // Удаляем лишние детали из display_name (город, район и т. п.)
            return preg_replace('/,.*$/', '', $place['display_name']);
        }
        return null;
    }

    private function isRelevantStreet(string $streetName, string $query): bool
    {
        // Список возможных суффиксов для улиц
        $suffixes = ['улица', 'ул.', 'проезд', 'пр.', 'переулок', 'пер.', 'набережная', 'наб.'];

        foreach ($suffixes as $suffix) {
            $pattern = '/^\s*' . preg_quote($query, '/') . '\s+' . preg_quote($suffix, '/') . '\s*$/';
            if (preg_match($pattern, $streetName)) {
                return true;
            }

            // Также проверяем вариант без суффикса (например, просто «Боровая»)
            $pattern = '/^\s*' . preg_quote($query, '/') . '\s*$/';
            if (preg_match($pattern, $streetName)) {
                return true;
            }
        }

        return false;
    }


}
