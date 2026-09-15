<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class StreetSearchDaDataService
{
    private function makeApiRequest(string $city, string $query)
    {
        $apiKey = config('services.dadata.api_key');
        $secretKey = config('services.dadata.secret_key');

        return Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Token ' . $apiKey,
                'X-Secret' => $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->retry(2, 1000, function ($exception) {
                return $exception instanceof ConnectionException ||
                    ($exception->response && $exception->response->status() >= 500);
            })
            ->post('https://dadata.ru/api/v2/suggest/address', [
                'query' => $query,
                'locations' => [
                    ['city' => $city]
                ],
                'from_bound' => ['value' => 'street'],
                'to_bound' => ['value' => 'street'],
                'count' => 10,
            ]);
    }

    public function searchStreets(string $city, string $query): JsonResponse
    {
        $cacheKey = 'streets_dadata_' . md5($city . '_' . $query);

        $result = Cache::remember($cacheKey, 86400, function () use ($city, $query) {
            try {
                $response = $this->makeApiRequest($city, $query);
                $data = $response->json();

                if (!$response->successful()) {
                    Log::channel('error_file')->error('DaData API error in street search', [
                        'city' => $city,
                        'query' => $query,
                        'status' => $response->status(),
                        'data' => $data
                    ]);
                    return ['error' => true, 'status' => $response->status(), 'message' => 'Ошибка при запросе к DaData.'];
                }

                Log::channel('info_file')->info('DaData street search completed successfully', [
                    'city' => $city,
                    'query' => $query
                ]);

                return ['error' => false, 'data' => $data];

            } catch (ConnectionException $e) {
                Log::channel('error_file')->error('Connection error to DaData for street search', [
                    'city' => $city,
                    'query' => $query,
                    'message' => $e->getMessage()
                ]);
                return ['error' => true, 'status' => 503, 'message' => 'Проблема с подключением к сервису геоданных.'];
            } catch (\Exception $e) {
                $correlationId = (string)Str::uuid();
                Log::channel('error_file')->error('Unexpected error in DaData street search service', [
                    'city' => $city,
                    'query' => $query,
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                    'correlation_id' => $correlationId
                ]);
                return ['error' => true, 'status' => 500, 'message' => 'Произошла внутренняя ошибка.', 'correlation_id' => $correlationId];
            }
        });

        if (isset($result['error']) && $result['error'] === true) {
            return response()->json([
                'error' => 'search_error',
                'message' => $result['message'] ?? 'Ошибка поиска улиц',
                'correlation_id' => $result['correlation_id'] ?? null
            ], $result['status'] ?? 500);
        }

        return $this->formatStreetsResponse($result['data'] ?? [], $query);
    }

    private function formatStreetsResponse($data, string $query): JsonResponse
    {
        $suggestions = $data['suggestions'] ?? [];

        if (empty($suggestions)) {
            return response()->json([]);
        }

        $streets = [];
        $streetNames = [];
        $queryLower = mb_strtolower($query, 'UTF-8');

        foreach ($suggestions as $item) {
            // Берём только улицу с типом (без города)
            $name = $item['data']['street_with_type']
                ?? $item['data']['street']
                ?? null;

            if (!$name) {
                continue;
            }

            $nameLower = mb_strtolower($name, 'UTF-8');

            // Фильтр: название должно содержать запрос
            if (mb_strpos($nameLower, $queryLower, 0, 'UTF-8') !== false) {
                if (!in_array($name, $streetNames)) {
                    $streets[] = [
                        'name' => $name,
                        'lat' => $item['data']['geo_lat'] ?? null,
                        'lon' => $item['data']['geo_lon'] ?? null,
                    ];
                    $streetNames[] = $name;
                }
            }
        }

        return response()->json($streets);
    }
}
