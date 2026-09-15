<?php

namespace App\Services;

use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class StreetSearchService
{
    private function makeApiRequest(string $city, string $query)
    {
        $fullQuery = $query . ', ' . $city;

        return Http::timeout(8)
            ->withHeaders(['User-Agent' => 'FeastBoom/1.0'])
            ->retry(3, 1000, function ($exception) {
                return $exception instanceof ConnectionException ||
                    ($exception->response && $exception->response->status() >= 500);
            })
            ->get('https://nominatim.openstreetmap.org/search', [
                'q'               => $fullQuery,
                'format'          => 'json',
                'addressdetails'  => 1,
                'limit'           => 10,
                'accept-language' => 'ru',
                'countrycodes'     => 'RU',
            ]);
    }

    public function searchStreets(string $city, string $query): JsonResponse
    {
        $cacheKey = 'streets_' . md5($city . '_' . $query);

        $result = Cache::remember($cacheKey, 86400, function () use ($city, $query) {
            try {

                $response = $this->makeApiRequest($city, $query);
                $data = $response->json();

                if (!$response->successful()) {
                    Log::channel('error_file')->error('Nominatim API error in street search', [
                        'city'   => $city,
                        'query'  => $query,
                        'status' => $response->status(),
                        'data'   => $data
                    ]);
                    return ['error' => true, 'status' => $response->status(), 'message' => 'Ошибка при запросе к сервису геоданных.'];
                }

                Log::channel('info_file')->info('Street search completed successfully', [
                    'city'  => $city,
                    'query' => $query
                ]);

                return ['error' => false, 'data' => $data];

            } catch (ConnectionException $e) {
                Log::channel('error_file')->error('Connection error to Nominatim for street search', [
                    'city'     => $city,
                    'query'    => $query,
                    'exception'=> $e::class,
                    'message'  => $e->getMessage()
                ]);
                return ['error' => true, 'status' => 503, 'message' => 'Проблема с подключением к сервису геоданных.'];
            } catch (RequestException $e) {
                Log::channel('error_file')->error('HTTP request error for street search', [
                    'city'      => $city,
                    'query'     => $query,
                    'exception' => $e::class,
                    'message'   => $e->getMessage()
                ]);
                return ['error' => true, 'status' => $e->response->status() ?? 500, 'message' => 'Ошибка при запросе к сервису геоданных.'];
            } catch (TransferException $e) {
                Log::channel('error_file')->error('Transfer error for street search', [
                    'city'      => $city,
                    'query'     => $query,
                    'exception' => $e::class,
                    'message'   => $e->getMessage()
                ]);
                return ['error' => true, 'status' => 504, 'message' => 'Ошибка передачи данных. Попробуйте позже.'];
            } catch (\Exception $e) {
                $correlationId = (string) Str::uuid();
                Log::channel('error_file')->error('Unexpected error in street search service', [
                    'city'           => $city,
                    'query'          => $query,
                    'exception'      => $e::class,
                    'message'        => $e->getMessage(),
                    'file'           => $e->getFile(),
                    'line'           => $e->getLine(),
                    'trace'          => $e->getTraceAsString(),
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
        if (!is_array($data) || empty($data)) {
            return response()->json([]);
        }

        $streets = [];
        $streetNames = [];
        $queryLower = mb_strtolower($query, 'UTF-8');

        foreach ($data as $place) {
            if (!is_array($place)) {
                continue;
            }

            $streetName = $this->extractStreetName($place);
            if (!$streetName) {
                continue;
            }

            if ($this->isRelevantStreet($streetName, $queryLower)) {
                if (!in_array($streetName, $streetNames)) {
                    $streets[] = [
                        'name' => $streetName,
                        'lat'  => $place['lat'] ?? null,
                        'lon'  => $place['lon'] ?? null
                    ];
                    $streetNames[] = $streetName;
                }
            }
        }

        return response()->json($streets);
    }

    private function extractStreetName(array $place): ?string
    {
        if (isset($place['address']['road'])) {
            return $place['address']['road'];
        }
        if (isset($place['display_name'])) {
            return preg_replace('/,.*$/', '', $place['display_name']);
        }
        return null;
    }

    private function isRelevantStreet(string $streetName, string $queryLower): bool
    {
        $streetNameLower = mb_strtolower($streetName, 'UTF-8');
        return mb_strpos($streetNameLower, $queryLower, 0, 'UTF-8') !== false;
    }
}
