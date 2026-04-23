<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Gate;
use App\Models\User;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Глобальная проверка: админ имеет доступ ко всем действиям
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // Универсальное правило для всех моделей
        Gate::define('can-access', function (User $user, $model) {
            // Случай 1: модель имеет поле user_id (прямая принадлежность)
            if (isset($model->user_id)) {
                if (is_null($model->user_id)) {
                    return false; // Если user_id null — доступ запрещён
                }
                return $user->id === $model->user_id;
            }

            // Случай 2: модель имеет отношение к другой модели с user_id
            // Перебираем все отношения модели
            foreach ($model->getRelations() as $relationName => $relation) {
                $related = $model->{$relationName};
                if ($related && isset($related->user_id) && !is_null($related->user_id)) {
                    return $user->id === $related->user_id;
                }
            }

            // Если ни одно условие не сработало — доступ запрещён
            return false;
        });
    }
}
