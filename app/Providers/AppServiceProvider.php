<?php

namespace App\Providers;

use App\Repositories\ObjRepository;
use App\Services\ObjService;
use App\Services\Service;
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
        // Сначала регистрируем репозиторий
        $this->app->singleton(ObjRepository::class, function () {
            return new ObjRepository();
        });

        // Затем регистрируем сервис с внедрением репозитория
        $this->app->singleton(ObjService::class, function ($app) {
            return new ObjService($app->make(ObjRepository::class));
        });

        $this->app->bind(Service::class, ObjService::class);
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
