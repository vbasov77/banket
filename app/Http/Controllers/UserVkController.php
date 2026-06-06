<?php

namespace App\Http\Controllers;

use App\Models\UserVk;
use App\Repositories\RequestRepository;
use App\Services\VkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserVkController extends Controller
{

    private RequestRepository $requestRepository;

    protected VkService $vkService;

    /**
     * @param RequestRepository $requestRepository
     */
    public function __construct(RequestRepository $requestRepository, VkService $vkService)
    {
        $this->requestRepository = $requestRepository;
        $this->vkService = $vkService;
    }


    public function redirectToVk()
    {
        $clientId = config('services.vk.client_id');
        $redirectUri = config('services.vk.redirect');
        $state = Str::random(40);
        session(['vk_state' => $state]);

        // Укажите нужные права — минимум email
        $scope = 'email';
        // Или расширенный набор: 'email,offline,friends'

        $encodedRedirectUri = urlencode($redirectUri);

        $url = "https://oauth.vk.com/authorize?client_id={$clientId}&display=page&redirect_uri={$encodedRedirectUri}&scope={$scope}&response_type=code&v=5.199&state={$state}";

        return redirect($url);
    }

    public function saveVkUserData(Request $request)
    {
        try {
            // Валидация входящих данных
            $validator = Validator::make($request->all(), [
                'access_token' => 'required|string',
                'refresh_token' => 'nullable|string',
                'expires_in' => 'required|integer|min:1',
                'user_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error_file')->error('Validation failed for VK user data', [
                    'errors' => $validator->errors(),
                    'vk_user_id' => $request->input('user_id'),
                    'timestamp' => now()->toDateTimeString()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Неверные данные пользователя VK',
                    'validation_errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            sleep(0.5);
            $params = [
                'uids' => $data['user_id'],
                'fields' => 'uid,first_name,last_name,screen_name,sex,bdate,photo_200,email',
                'access_token' => $data['access_token'],
                'v' => '5.199'];
            $url = "https://api.vk.com/method/users.get";

            $userInfo = json_decode($this->requestRepository->post($url, $params), true);

            $newToken = $this->vkService->refreshVkAccessToken($data['refresh_token']);
            Log::channel('info_file')->info($newToken);

            // Расчёт времени истечения токена
            try {
                $expiresAt = now()->addSeconds($data['expires_in']);
            } catch (\Exception $e) {
                Log::channel('error_file')->error('Invalid expires_in value', [
                    'expires_in' => $data['expires_in'],
                    'vk_user_id' => $data['user_id'],
                    'exception' => $e->getMessage()
                ]);
                throw new \Exception('Некорректное значение expires_in');
            }

            // Явный поиск существующей записи
            $existingUser = UserVk::where('vk_id', $data['user_id'])->first();


            if ($existingUser) {
                // Обновление существующей записи
                $existingUser->update([
                    'user_id' => Auth::id(),
                    'first_name' => $userInfo['response'][0]['first_name'] ?? null,
                    'last_name' => $userInfo['response'][0]['last_name'] ?? null,
                    'email' => $userInfo['response'][0]['email'] ?? null,
                    'bdate' => $userInfo['response'][0]['bdate'] ?? null,
                    'photo' => $userInfo['response'][0]['photo_200'] ?? null,
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'token_expires_at' => $expiresAt
                ]);
                $user = $existingUser;
                $isNew = false;
            } else {
                // Создание новой записи
                $user = UserVk::create([
                    'vk_id' => $data['user_id'],
                    'user_id' => Auth::id(),
                    'first_name' => $userInfo['response'][0]['first_name'] ?? null,
                    'last_name' => $userInfo['response'][0]['last_name'] ?? null,
                    'email' => $userInfo['response'][0]['email'] ?? null,
                    'bdate' => $userInfo['response'][0]['bdate'] ?? null,
                    'photo' => $userInfo['response'][0]['photo_200'] ?? null,
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'token_expires_at' => $expiresAt
                ]);
                $isNew = true;
            }



            return response()->json([
                'success' => true,
                'message' => $isNew ? 'Данные пользователя VK успешно созданы' : 'Данные пользователя VK успешно обновлены',
                'user' => $user,
                'is_new' => $isNew
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('error_file')->error('Database error saving VK user data', [
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'vk_user_id' => $request->input('user_id'),
                'internal_user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Ошибка базы данных. Попробуйте снова.'
            ], 500);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Unexpected error saving VK user data', [
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'vk_user_id' => $request->input('user_id'),
                'internal_user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера при сохранении данных'
            ], 500);
        }
    }

    public function sendHelloMessageToVk(Request $request)
    {
        $vkUserId = 642803932; // VK ID пользователя

        // Получаем токен доступа из БД
        $userVk = UserVk::where('vk_id', $vkUserId)->first();
        if (!$userVk || !$userVk->access_token) {
            return response()->json([
                'success' => false,
                'error' => 'Токен доступа VK не найден'
            ], 400);
        }

        try {
            // Отправляем сообщение через VK API
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('https://api.vk.com/method/messages.send', [
                'user_id' => 142418382,
                'message' => 'Привет! Добро пожаловать в наше приложение!',
                'access_token' => $userVk->access_token,
                'v' => '5.131' // Версия API VK
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Сообщение успешно отправлено в VK'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка при отправке сообщения в VK',
                    'vk_error' => $response->json()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка отправки сообщения в VK: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера'
            ], 500);
        }
    }


}
