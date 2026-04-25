<?php

namespace App\Providers;

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
        // При необходимости добавьте сопоставления политик здесь
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
            // Случай 1: прямая принадлежность через user_id
            if (isset($model->user_id)) {
                if (is_null($model->user_id)) {
                    return false; // Если user_id null — доступ запрещён
                }
                return $user->id === $model->user_id;
            }

            // Случай 2: специальная логика для модели Subj (Subj → Obj → user_id)
            if ($model instanceof \App\Models\Subj) {
                try {
                    // Загружаем связь obj, если она не загружена
                    if (!$model->relationLoaded('obj')) {
                        $model = $model->load('obj');
                    }

                    $obj = $model->obj;
                    if ($obj && isset($obj->user_id) && !is_null($obj->user_id)) {
                        return $user->id === $obj->user_id;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }

            // Случай 3: проверка для других распространённых отношений (user, owner, author)
            $commonRelations = ['user', 'owner', 'author'];
            foreach ($commonRelations as $relation) {
                if ($model->relationLoaded($relation)) {
                    $related = $model->{$relation};
                    if ($related && isset($related->id)) {
                        return $user->id === $related->id;
                    }
                }
            }

            // Случай 4: проверка всех отношений модели через рефлексию
            $reflection = new \ReflectionClass($model);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);


            foreach ($methods as $method) {
                $methodName = $method->getName();

                // Пропускаем магические методы и служебные методы
                if (str_starts_with($methodName, '_') ||
                    in_array($methodName, ['getKey', 'getKeyName', 'getRouteKey', 'getRelations'])) {
                    continue;
                }

                try {
                    $returnValue = $model->$methodName();

                    // Проверяем, является ли метод отношением Eloquent
                    if ($returnValue instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                        // Загружаем отношение, если оно не загружено
                        if (!$model->relationLoaded($methodName)) {
                            $model->load($methodName);
                        }

                        $related = $model->{$methodName};

                        // Для коллекций перебираем все элементы
                        if ($related instanceof \Illuminate\Database\Eloquent\Collection) {
                            foreach ($related as $item) {
                                if (isset($item->user_id) && !is_null($item->user_id)) {
                                    return $user->id === $item->user_id;
                                }
                            }
                        } elseif ($related && isset($related->user_id) && !is_null($related->user_id)) {
                            return $user->id === $related->user_id;
                        }
                    }
                } catch (\Exception $e) {
                    // Игнорируем ошибки загрузки отношений
                    continue;
                }
            }

// Если ни одно условие не сработало — доступ запрещён
            return false;
        });
    }
}
