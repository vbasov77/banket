<?php

namespace App\Http\Controllers;

use App\Exceptions\UserNotFoundException;
use App\Services\FavoriteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    private FavoriteService $favoriteService;


    // Добавление ресторана в избранное

    /**
     * @param FavoriteService $favoriteService
     */
    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::channel('error_file')->error(
                    'Попытка добавления в избранное без авторизации в FavoriteController@store',
                    [
                        'request_data' => $request->all(),
                        'ip' => $request->ip()
                    ]
                );
                return response()->json(['error' => 'Пользователь не авторизован'], 401);
            }

            $this->favoriteService->addToFavorites($user->id, $request->id);

            return response()->json(['message' => 'Добавлено в избранное']);
        } catch (ModelNotFoundException $e) {
            Log::channel('error_file')->error(
                'Ресторан не найден в FavoriteController@store: ID ' . $request->id,
                [
                    'requested_restaurant_id' => $request->id,
                    'user_id' => $user?->id,
                    'ip' => $request->ip()
                ]
            );
            return response()->json(['error' => 'Ресторан не найден'], 404);
        } catch (\App\Exceptions\AlreadyInFavoritesException $e) {
            Log::channel('error_file')->error(
                'Объект уже в избранном в FavoriteController@store',
                [
                    'user_id' => $user?->id,
                    'restaurant_id' => $request->id
                ]
            );
            return response()->json(['message' => 'Уже в избранном'], 200);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в FavoriteController@store: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'user_id' => $user?->id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]
            );
            return response()->json(['error' => 'Произошла внутренняя ошибка сервера'], 500);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::channel('error_file')->error(
                    'Попытка удаления из избранного без авторизации в FavoriteController@destroy',
                    [
                        'request_data' => $request->all(),
                        'ip' => $request->ip()
                    ]
                );
                return response()->json(['error' => 'Не авторизован'], 401);
            }

            $this->favoriteService->removeFromFavorites($user->id, $request->id);

            return response()->json(['message' => 'Удалено из избранного'], 201);
        } catch (\App\Exceptions\FavoriteNotFoundException $e) {
            Log::channel('error_file')->error(
                'Объект не найден в избранном в FavoriteController@destroy',
                [
                    'user_id' => $user?->id,
                    'restaurant_id' => $request->id
                ]
            );
            return response()->json(['error' => 'Не найдено в избранном'], 404);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в FavoriteController@destroy: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'input_data' => $request->all(),
                    'user_id' => $user?->id,
                    'ip' => $request->ip()
                ]
            );
            return response()->json(['error' => 'Ошибка при удалении из базы данных'], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в FavoriteController@destroy: ' . $e->getMessage(),
                [
                    'input_data' => $request->all(),
                    'user_id' => $user?->id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => $request->ip()
                ]
            );
            return response()->json(['error' => 'Произошла внутренняя ошибка сервера'], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::channel('error_file')->error(
                    'Попытка просмотра избранного без авторизации в FavoriteController@index',
                    [
                        'ip' => request()->ip()
                    ]
                );
                return response()->json(['error' => 'Не авторизован'], 401);
            }

            $favorites = $this->favoriteService->getFavoriteRestaurants($user->id);

            return view('favorites.index', ['favorites' => $favorites]);
        } catch (UserNotFoundException $e) {
            Log::channel('error_file')->error(
                'Пользователь не найден в FavoriteController@index',
                [
                    'user_id' => $user?->id,
                    'ip' => request()->ip()
                ]
            );
            return response()->json(['error' => 'Пользователь не найден'], 404);
        } catch (QueryException $e) {
            Log::channel('error_file')->error(
                'SQL ошибка в FavoriteController@index: ' . $e->getMessage(),
                [
                    'sql_query' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'user_id' => $user?->id,
                    'ip' => request()->ip()
                ]
            );
            return response()->json(['error' => 'Ошибка при получении данных из базы данных'], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Неожиданная ошибка в FavoriteController@index: ' . $e->getMessage(),
                [
                    'user_id' => $user?->id,
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                    'ip' => request()->ip()
                ]
            );
            return response()->json(['error' => 'Произошла внутренняя ошибка сервера'], 500);
        }
    }

}
