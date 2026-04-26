<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\City;
use App\Models\District;
use Illuminate\Support\Facades\Log;

class CityDistrictService
{
    /**
     * Создаёт город и районы или добавляет районы к существующему городу
     */
    public function storeCityAndDistricts(array $data): array
    {
        try {
            $cityName = trim($data['city_name']);
            $districtsInput = $data['districts'];

            return DB::transaction(function () use ($cityName, $districtsInput) {
                // Проверка существования города
                $existingCity = City::where('name', $cityName)->first();

                if ($existingCity) {
                    $city = $existingCity;
                    $message = 'Районы успешно добавлены к существующему городу!';
                } else {
                    $city = City::create([
                        'name' => $cityName,
                    ]);
                    $message = 'Город и районы успешно добавлены!';
                }

                // Обрабатываем многострочный ввод районов с ";" в конце
                $districtLines = preg_split('/\r\n|\r|\n/', $districtsInput);
                $districtNames = [];

                foreach ($districtLines as $line) {
                    $cleanLine = trim(rtrim($line, ';'));
                    if (!empty($cleanLine)) {
                        $districtNames[] = $cleanLine;
                    }
                }

                // Сохраняем районы с проверкой дубликатов
                $addedDistrictsCount = 0;
                $skippedDistrictsCount = 0;

                foreach ($districtNames as $districtName) {
                    // Используем firstOrCreate для атомарной проверки и создания
                    $district = District::firstOrCreate(
                        ['city_id' => $city->id, 'name' => $districtName],
                        [] // дополнительные поля для создания (если нужны)
                    );

                    if ($district->wasRecentlyCreated) {
                        $addedDistrictsCount++;
                    } else {
                        $skippedDistrictsCount++;
                    }
                }

                // Формируем итоговое сообщение с учётом статистики
                if ($addedDistrictsCount > 0) {
                    $message .= " Добавлено районов: {$addedDistrictsCount}.";
                }
                if ($skippedDistrictsCount > 0) {
                    $message .= " Пропущено существующих районов: {$skippedDistrictsCount}.";
                }

                return [
                    'success' => true,
                    'message' => $message,
                    'city' => $city,
                    'added_count' => $addedDistrictsCount,
                    'skipped_count' => $skippedDistrictsCount
                ];
            });
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error('Database error in CityDistrictService::storeCityAndDistricts', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'input' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при работе с базой данных. Пожалуйста, попробуйте позже.',
                'error_type' => 'database',
                'exception' => $e
            ];
        } catch (\Exception $e) {
            Log::channel('error_file')->critical('Unexpected error in CityDistrictService::storeCityAndDistricts', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Произошла внутренняя ошибка. Пожалуйста, попробуйте позже.',
                'error_type' => 'application',
                'exception' => $e
            ];
        } catch (\Throwable $e) {
            Log::channel('error_file')->emergency('Fatal error in CityDistrictService::storeCityAndDistricts', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Произошла критическая ошибка на сервере. Пожалуйста, обратитесь к администратору.',
                'error_type' => 'fatal',
                'exception' => $e
            ];
        }
    }
}
