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


class StreetSearchYaService
{
    private function makeApiRequest(string $city, string $query)
    {
        $fullQuery = $query . ', ' . $city;
        $apiKey = config('services.yandex_maps.key');

        return Http::timeout(10)
            ->withHeaders(['User-Agent' => 'FeastBoom/1.0'])
            ->retry(2, 1000, function ($exception) {
                return $exception instanceof ConnectionException ||
                    ($exception->response && $exception->response->status() >= 500);
            })
            ->get('https://geocode-maps.yandex.ru/1.x/', [
                'apikey' => $apiKey,
                'geocode' => $fullQuery,
                'format'  => 'json',
                'lang'    => 'ru_RU',
                'results' => 10,
                'kind'    => 'street',
            ]);
    }

    public function searchStreets(string $city, string $query): JsonResponse
    {
        $cacheKey = 'streets_yandex_' . md5($city . '_' . $query);

        $result = Cache::remember($cacheKey, 86400, function () use ($city, $query) {
            try {
                Log::channel('info_file')->info('Starting Yandex street search', [
                    'city'  => $city,
                    'query' => $query
                ]);

                $response = $this->makeApiRequest($city, $query);
                $data = $response->json();

                if (!$response->successful()) {
                    Log::channel('error_file')->error('Yandex API error in street search', [
                        'city'   => $city,
                        'query'  => $query,
                        'status' => $response->status(),
                        'data'   => $data
                    ]);
                    return ['error' => true, 'status' => $response->status(), 'message' => 'Ошибка при запросе к Яндекс.Геокодеру.'];
                }

                Log::channel('info_file')->info('Yandex street search completed successfully', [
                    'city'  => $city,
                    'query' => $query
                ]);

                return ['error' => false, 'data' => $data];

            } catch (ConnectionException $e) {
                Log::channel('error_file')->error('Connection error to Yandex for street search', [
                    'city'     => $city,
                    'query'    => $query,
                    'exception'=> $e::class,
                    'message'  => $e->getMessage()
                ]);
                return ['error' => true, 'status' => 503, 'message' => 'Проблема с подключением к сервису геоданных.'];
            } catch (RequestException $e) {
                Log::channel('error_file')->error('HTTP request error for Yandex street search', [
                    'city'      => $city,
                    'query'     => $query,
                    'exception' => $e::class,
                    'message'   => $e->getMessage()
                ]);
                return ['error' => true, 'status' => $e->response->status() ?? 500, 'message' => 'Ошибка при запросе к Яндекс.Геокодеру.'];
            } catch (TransferException $e) {
                Log::channel('error_file')->error('Transfer error for Yandex street search', [
                    'city'      => $city,
                    'query'     => $query,
                    'exception' => $e::class,
                    'message'   => $e->getMessage()
                ]);
                return ['error' => true, 'status' => 504, 'message' => 'Ошибка передачи данных. Попробуйте позже.'];
            } catch (\Exception $e) {
                $correlationId = (string) Str::uuid();
                Log::channel('error_file')->error('Unexpected error in Yandex street search service', [
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
                'error'           => 'search_error',
                'message'         => $result['message'] ?? 'Ошибка поиска улиц',
                'correlation_id'  => $result['correlation_id'] ?? null
            ], $result['status'] ?? 500);
        }

        return $this->formatStreetsResponse($result['data'] ?? [], $query);
    }

    private function formatStreetsResponse($data, string $query): JsonResponse
    {
        // Яндекс возвращает данные по пути: response -> GeoObjectCollection -> featureMember
        $members = $data['response']['GeoObjectCollection']['featureMember'] ?? [];

        if (empty($members)) {
            return response()->json([]);
        }

        $streets = [];
        $streetNames = [];
        $queryLower = mb_strtolower($query, 'UTF-8');

        foreach ($members as $member) {
            $geoObject = $member['GeoObject'] ?? null;
            if (!$geoObject) {
                continue;
            }

            $name = $geoObject['name'] ?? null;
            if (!$name) {
                continue;
            }

            $nameLower = mb_strtolower($name, 'UTF-8');

            // Фильтруем: название должно содержать запрос
            if (mb_strpos($nameLower, $queryLower, 0, 'UTF-8') !== false) {
                if (!in_array($name, $streetNames)) {
                    // Координаты Яндекса приходят строкой "долгота,широта" (через пробел)
                    $pos = explode(' ', $geoObject['Point']['pos'] ?? '');
                    $streets[] = [
                        'name' => $name,
                        'lat'  => $pos[1] ?? null, // широта
                        'lon'  => $pos[0] ?? null,  // долгота
                    ];
                    $streetNames[] = $name;
                }
            }
        }

        return response()->json($streets);
    }
}
