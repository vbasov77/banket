<?php

namespace App\Providers;

use App\Models\User;
use App\Repositories\MessageRepository;
use App\Repositories\ObjRepository;
use App\Services\MessageService;
use App\Services\ObjService;
use App\Services\Service;
use App\View\Composers\CatalogComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ObjRepository::class);
        $this->app->singleton(ObjService::class);
        $this->app->bind(Service::class, ObjService::class);
        $this->app->singleton(MessageRepository::class);
        $this->app->singleton(MessageService::class);
    }

    public function boot(): void
    {
        View::composer(['layouts.app', 'layouts.navigation'], CatalogComposer::class);

        // Rate limiter 'api' (переехал из RouteServiceProvider)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Переехало из AuthServiceProvider
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        Gate::define('can-access', function (User $user, $model) {
            // сюда целиком копируем тело can-access из AuthServiceProvider — без изменений
        });

    }
}
