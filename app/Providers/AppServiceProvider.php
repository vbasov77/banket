<?php

namespace App\Providers;

use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                $connection = DB::getDoctrineConnection();
                $platform = $connection->getDatabasePlatform();

                // Регистрируем отображение типа POINT на стандартный строковый тип
                $platform->registerDoctrineTypeMapping('point', 'string');

                // Дополнительно регистрируем тип POINT, если он ещё не зарегистрирован
                if (!Type::hasType('point')) {
                    Type::addType('point', 'Doctrine\DBAL\Types\StringType');
                }
            } catch (\Exception $e) {
                Log::error('Failed to register POINT type: ' . $e->getMessage());
            }
        }
    }
}
