<?php

namespace App\Services;

use App\Exceptions\AlreadyInFavoritesException;
use App\Exceptions\FavoriteNotFoundException;
use App\Exceptions\UserNotFoundException;
use App\Models\FavoriteSubj;
use App\Models\Subj;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class FavoriteService
{
    /**
     * @throws AlreadyInFavoritesException
     */
    public function addToFavorites(int $userId, int $restaurantId): void
    {
        try {
            // Проверяем существование ресторана
            $restaurant = Subj::find($restaurantId);
            if (!$restaurant) {
                throw new ModelNotFoundException("Ресторан с ID {$restaurantId} не найден");
            }

            // Проверяем, не добавлен ли уже
            $existing = FavoriteSubj::where('user_id', $userId)
                ->where('subj_id', $restaurantId)
                ->first();

            if ($existing) {
                throw new AlreadyInFavoritesException("Объект уже в избранном для пользователя {$userId}");
            }

            // Добавляем в избранное
            FavoriteSubj::create([
                'user_id' => $userId,
                'subj_id' => $restaurantId
            ]);
        } catch (ModelNotFoundException|AlreadyInFavoritesException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в FavoriteService@addToFavorites: ' . $e->getMessage(),
                [
                    'user_id' => $userId,
                    'restaurant_id' => $restaurantId,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

    /**
     * @throws FavoriteNotFoundException
     */
    public function removeFromFavorites(int $userId, int $restaurantId): void
    {
        try {
            // Ищем запись в избранном
            $favorite = FavoriteSubj::where('user_id', $userId)
                ->where('subj_id', $restaurantId)
                ->first();

            if (!$favorite) {
                throw new FavoriteNotFoundException(
                    "Запись в избранном не найдена для пользователя {$userId} и ресторана {$restaurantId}"
                );
            }

            // Удаляем из избранного
            $deleted = $favorite->delete();

            if (!$deleted) {
                Log::channel('error_file')->error(
                    'Не удалось удалить запись из избранного в FavoriteService@removeFromFavorites',
                    [
                        'user_id' => $userId,
                        'restaurant_id' => $restaurantId
                    ]
                );
                throw new Exception('Не удалось удалить запись из базы данных');
            }
        } catch (FavoriteNotFoundException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в FavoriteService@removeFromFavorites: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'user_id' => $userId,
                    'restaurant_id' => $restaurantId
                ]
            );
            throw $e;
        } catch (Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в FavoriteService@removeFromFavorites: ' . $e->getMessage(),
                [
                    'user_id' => $userId,
                    'restaurant_id' => $restaurantId,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }

    /**
     * @throws UserNotFoundException
     */
    public function getFavoriteRestaurants(int $userId): array
    {
        try {
            // Находим пользователя
            $user = User::find($userId);
            if (!$user) {
                throw new UserNotFoundException("Пользователь с ID {$userId} не найден");
            }

            // Получаем избранные рестораны с изображениями
            $favorites = $user->favoriteRestaurants()
                ->with('primaryImg')
                ->get()
                ->toArray();

            return $favorites;
        } catch (UserNotFoundException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в FavoriteService@getFavoriteRestaurants: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'user_id' => $userId
                ]
            );
            throw $e;
        } catch (Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка в FavoriteService@getFavoriteRestaurants: ' . $e->getMessage(),
                [
                    'user_id' => $userId,
                    'exception_class' => get_class($e)
                ]
            );
            throw $e;
        }
    }
}
