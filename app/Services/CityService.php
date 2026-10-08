<?php

namespace App\Services;

use App\Models\City;
use App\Models\Region;
use App\Models\UserCity;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CityService
{
    /**
     * Получает список всех городов
     */
    public function getCities(): array
    {
        try {
            $cities = City::all();

            if ($cities->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Городов не найдено в базе данных.',
                    'cities' => [],
                    'count' => 0,
                    'http_status' => 200
                ];
            }

            $responseData = $cities->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name
                ];
            });

            return [
                'success' => true,
                'message' => 'Список городов получен успешно.',
                'cities' => $responseData,
                'count' => $cities->count(),
                'http_status' => 200
            ];

        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error('Database query error while retrieving cities', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при получении данных из базы данных.',
                'details' => [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage()
                ],
                'http_status' => 500
            ];

        } catch (\Exception $e) {
            Log::channel('error_file')->critical('Unexpected error in CityService::getCities', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Внутренняя ошибка сервера.',
                'details' => [
                    'error' => $e->getMessage()
                ],
                'http_status' => 500
            ];

        } catch (\Throwable $e) {
            Log::channel('error_file')->emergency('Fatal error in CityService::getCities', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Произошла критическая ошибка на сервере. Пожалуйста, попробуйте позже.',
                'error_code' => 'FATAL_ERROR',
                'http_status' => 500
            ];
        }
    }

    /**
     * Устанавливает город для пользователя (авторизованного или гостя)
     */
    public function setCity(Request $request): array
    {
        try {
            // Валидация входных данных
            $validated = $request->validate([
                'city' => 'required|string|min:1|max:255',
                'city_id' => 'required|integer|min:1',
            ]);

            $cityName = $validated['city'];
            $cityId = $validated['city_id'];

            // Проверка существования города
            $cityExists = City::where('id', $cityId)->exists();
            if (!$cityExists) {
                Log::channel('error_file')->warning('Attempt to set non-existent city', [
                    'city_id' => $cityId,
                    'city_name' => $cityName,
                ]);
                return [
                    'success' => false,
                    'message' => 'Указанный город не найден в системе',
                    'details' => [
                        'provided_city_id' => $cityId,
                        'provided_city_name' => $cityName,
                    ],
                    'http_status' => 404
                ];
            }

            // Очистка и установка сессии
            Session::forget('selected_filters');
            Session::put('user_city', $cityName);
            Session::put('city_id', $cityId);

            $regionId = City::find($cityId)?->region_id;
            Session::put('region_ids', $regionId ? [$regionId] : [1, 2]);
            $request->session()->save();

            // Если пользователь не авторизован
            if (!Auth::check()) {
                return [
                    'success' => true,
                    'message' => 'Город успешно установлен',
                    'data' => [
                        'city' => $cityName,
                        'city_id' => $cityId,
                        'user_type' => 'guest'
                    ],
                    'http_status' => 200
                ];
            }

            // Для авторизованных пользователей — сохраняем связь с городом
            try {
                $updateResult = UserCity::updateOrCreate(
                    ['user_id' => Auth::id()],
                    ['city_id' => $cityId]
                );

                return [
                    'success' => true,
                    'message' => 'Город успешно сохранён для пользователя',
                    'data' => [
                        'user_id' => Auth::id(),
                        'city' => $cityName,
                        'city_id' => $cityId,
                        'action' => $updateResult->wasRecentlyCreated ? 'created' : 'updated'
                    ],
                    'http_status' => 200
                ];

            } catch (QueryException $e) {
                Log::channel('error_file')->error('Database error while saving user city preference', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'query' => $e->getQuery(),
                    'bindings' => $e->getBindings(),
                    'user_id' => Auth::id(),
                    'city_id' => $cityId,
                ]);

                return [
                    'success' => false,
                    'message' => 'Произошла ошибка при сохранении города в базе данных',
                    'details' => [
                        'error_code' => $e->getCode(),
                        'error_type' => 'database_error',
                        'technical_message' => $e->getMessage()
                    ],
                    'http_status' => 500
                ];
            } catch (\Exception $e) {
                Log::channel('error_file')->critical('Unexpected error while saving user city', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user_id' => Auth::id(),
                    'city_id' => $cityId,
                ]);

                return [
                    'success' => false,
                    'message' => 'Произошла внутренняя ошибка при сохранении города',
                    'details' => [
                        'error' => $e->getMessage(),
                        'error_type' => 'application_error'
                    ],
                    'http_status' => 500
                ];
            }

        } catch (ValidationException $e) {
            Log::channel('error_file')->warning('Validation error in CityService::setCity', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка валидации данных',
                'errors' => $e->errors(),
                'details' => [
                    'validation_rules' => [
                        'city' => 'обязательное строковое поле (1–255 символов)',
                        'city_id' => 'обязательное целое число ≥ 1'
                    ],
                ],
                'http_status' => 422
            ];
        } catch (\Throwable $e) {
            Log::channel('error_file')->emergency('Fatal error in CityService::setCity', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);

            return [
                'success' => false,
                'message' => 'Произошла критическая ошибка на сервере. Пожалуйста, попробуйте позже.',
                'error_code' => 'FATAL_ERROR',
                'http_status' => 500
            ];
        }
    }

    public function findCity(string $query, int $limit = 10): array
    {
        $cleanQuery = trim($query);

        // Валидация запроса
        if (empty($cleanQuery)) {
            return [];
        }

        if (strlen($cleanQuery) < 2) {
            return [];
        }

        // Проверка на недопустимые символы (опционально)
        if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $cleanQuery)) {
            return [];
        }

        try {
            $cities = City::select('id', 'name')
                ->where('name', 'like', '%' . $cleanQuery . '%')
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                    ];
                })
                ->toArray(); // Преобразуем в массив

            return $cities;
        } catch (QueryException $e) {
            Log::error('Database error in findCity', [
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Unexpected error in findCity', [
                'query' => $query,
                'exception' => $e::class,
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function findUserCity(Request $request)
    {
        if (Auth::check()) {
            $city = UserCity::where('user_id', Auth::user()->id)->first();
            if ($city !== null) {
                $nameCity = City::where('id', $city->city_id)->value('name');
                $request->session()->put('user_city', $nameCity);
                $request->session()->save();
            }
        }
    }

    public function getCitiesByRegion(Region $region): array
    {
        $cities = City::query()
            ->join('address_subjs', 'address_subjs.city_id', '=', 'cities.id')
            ->join('subjs', fn($join) => $join
                ->on('subjs.id', '=', 'address_subjs.subj_id')
                ->where('subjs.published', 1))
            ->join('objs', 'objs.id', '=', 'subjs.obj_id')
            ->where('cities.region_id', $region->id)
            ->groupBy('cities.id', 'cities.name')
            ->selectRaw('cities.id, cities.name, COUNT(DISTINCT objs.id) as subjs_count')
            ->orderByDesc('subjs_count')
            ->get();

        $single = $cities->count() === 1 ? $cities->first() : null;

        return [
            'success' => true,
            'data' => $cities,
            'needs_city_choice' => $cities->count() > 1,
            'city' => $single ? ['id' => $single->id, 'name' => $single->name] : null,
            'http_status' => 200,
        ];
    }

    public function getRegions(): array
    {
        $regions = Region::query()
            ->join('cities', 'cities.region_id', '=', 'regions.id')
            ->join('address_subjs', 'address_subjs.city_id', '=', 'cities.id')
            ->join('subjs', fn($join) => $join
                ->on('subjs.id', '=', 'address_subjs.subj_id')
                ->where('subjs.published', 1))
            ->join('objs', 'objs.id', '=', 'subjs.obj_id')
            ->groupBy('regions.id', 'regions.name')
            ->selectRaw('regions.id, regions.name, COUNT(DISTINCT objs.id) as subjs_count')
            ->orderByDesc('subjs_count')
            ->get();

        return ['success' => true, 'data' => $regions, 'http_status' => 200];
    }

    public function setRegion(Request $request): array
    {
        try {
            $validated = $request->validate([
                'city_id' => ['required', 'integer', 'exists:cities,id'],
            ]);

            $city = City::with('region')->findOrFail($validated['city_id']);

            Session::forget('selected_filters');
            Session::forget('region_ids'); // ключ старой концепции — подчистить
            Session::put('user_city', $city->name);
            Session::put('city_id', $city->id);
            $request->session()->save();

            if (Auth::check()) {
                UserCity::updateOrCreate(
                    ['user_id' => Auth::id()],
                    ['city_id' => $city->id]
                );
            }

            return [
                'success' => true,
                'data' => [
                    'city' => $city->name,
                    'region_name' => $city->region->name,
                    'user_type' => Auth::check() ? 'user' : 'guest',
                ],
                'http_status' => 200,
            ];
        } catch (ValidationException $e) {
            return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $e->errors(), 'http_status' => 422];
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Ошибка в CityService::setRegion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Внутренняя ошибка сервера', 'http_status' => 500];
        }
    }




}
