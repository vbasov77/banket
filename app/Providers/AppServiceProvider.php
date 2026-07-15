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
        // --- Твой существующий код для Doctrine (оставляем как есть) ---
        if (class_exists(\Doctrine\DBAL\Types\Type::class)) {
            try {
                $connection = DB::connection();
                if ($connection->getDriverName() === 'mysql') {
                    $doctrineConnection = $connection->getDoctrineConnection();
                    $platform = $doctrineConnection->getDatabasePlatform();

                    $platform->registerDoctrineTypeMapping('point', 'string');

                    if (! \Doctrine\DBAL\Types\Type::hasType('point')) {
                        \Doctrine\DBAL\Types\Type::addType(
                            'point',
                            \Doctrine\DBAL\Types\StringType::class
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Failed to register POINT type for MySQL: ' . $e->getMessage());
            }
        }
        $this->app['router']->aliasMiddleware('ensureRole', \App\Http\Middleware\EnsureRole::class);
    }
}
