<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;

use App\Http\Requests\Catalog\StorePerformerRequest;
use App\Http\Requests\Catalog\UpdatePerformerRequest;
use App\Models\CatalogCategory;
use App\Models\Performer;
use App\Models\PerformerPhoto;
use App\Models\UserContact;
use App\Services\PerformerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PerformerRequestController extends Controller
{
    /**
     * @param PerformerService $service
     */
    public function __construct(private PerformerService $service) {}

    /**
     * @return View
     */
    public function create(): View
    {
        return view('catalog.request.create', [
            'categories' => $this->activeCategories(),
        ]);
    }

    /**
     * @param StorePerformerRequest $request
     * @return RedirectResponse
     */
    public function store(StorePerformerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Владелец — текущий пользователь, публикация только после модерации
        $data['user_id'] = $request->user()->id;
        $data['published'] = 0;

        $performer = $this->service->create($data);

        return redirect()
            ->route('catalog.request.edit', ['performer' => $performer->id])
            ->with('messages', 'Заявка отправлена. Опубликуем после проверки.');
    }


    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function myCategory(Request $request): View|RedirectResponse
    {
        // Гарантируем, что пользователь есть (благодаря middleware 'auth' это почти всегда верно)
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $objects = $this->service->listAllForUser($user->id);

            return view('catalog.request.my', compact('objects'));
        } catch (\Exception $exception) {
            // Пишем в твой кастомный канал логов
            Log::channel('error_file')->error(
                'Failed to load user objects for user_id: ' . $user->id,
                ['exception' => $exception->getMessage()]
            );

            // Возвращаем страницу, но с сообщением об ошибке, которое подхватит твой Blade
            return view('catalog.request.my', [
                'objects' => collect(), // Пустая коллекция, чтобы цикл @foreach не сломался
                'error'   => 'Не удалось получить список объектов. Сервер временно недоступен.',
            ]);
        }
    }

    /**
     * @param Request $request
     * @param int $performerId
     * @return View
     */
    public function edit(Request $request, int $performerId): View
    {
        $performer = $this->service->findOwnedByUser($performerId, $request->user()->id);

        return view('catalog.request.edit', [
            'performer'  => $performer,
            'categories' => $this->activeCategories(),
        ]);
    }

    /**
     * @param UpdatePerformerRequest $request
     * @param int $performerId
     * @return RedirectResponse
     */
    public function update(UpdatePerformerRequest $request, int $performerId): RedirectResponse
    {
        $performer = $this->service->findOwnedByUser($performerId, $request->user()->id);

        $data = $request->validated();
        unset($data['published']); // статус меняет только админ

        $this->service->update($performer, $data);

        return redirect()
            ->route('catalog.request.my_category')
            ->with('success', 'Изменения сохранены');
    }

    /**
     * @return Collection
     */
    private function activeCategories(): Collection
    {
        return CatalogCategory::where('published', 1)
            ->orderBy('position')
            ->get();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function takeOff(Request $request): JsonResponse
    {
        try {
            $cat = Performer::findOrFail($request->id);

            // Проверка прав доступа через метод модели isAuthor()
            if (!$cat->isAuthor()) {
                Log::channel('error_file')->error('Unauthorized subj edit attempt', [
                    'performer_id' => $cat->id,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'answer' => 'error',
                    'message' => 'У вас нет прав для выполнения этого действия'
                ], 403);
            }

            $cat->update(['published' => 0]);

            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация снята'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException  $e) {
            return response()->json([
                'answer' => 'error',
                'message' => 'Объект не найден'
            ], 404);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in takeOff', [
                'exception' => $e->getMessage(),
                'performer_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'answer' => 'error',
                'message' => 'Произошла ошибка при снятии публикации'
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function published(Request $request): JsonResponse
    {
        $performerId = (int) $request->id;


        $img = PerformerPhoto::where('performer_id', $performerId)->exists();
        if (!$img) {
            return response()->json([
                'answer' => 'no',
                'message' => 'Сначала добавьте фото' // Исправлено сообщение
            ]);
        }


        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'answer' => 'error',
                    'message' => 'Неавторизованный доступ'
                ], 401);
            }

            $cat = Performer::findOrFail($performerId);

            $contact = UserContact::where('user_id', $cat->user_id)->exists();
            if (!$contact) {
                return response()->json([
                    'answer' => 'no',
                    'message' => 'Сначала добавьте контакты в разделе Мои контакты' // Исправлено сообщение
                ]);
            }

            // Проверка прав доступа через метод модели isAuthor()
            if (!$cat->isAuthor()) {
                Log::channel('error_file')->error('Unauthorized subj edit attempt', [
                    'performer_id' => $performerId,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'answer' => 'error',
                    'message' => 'У вас нет прав для выполнения этого действия'
                ], 403);
            }

            $cat->update(['published' => 1]);

            return response()->json([
                'answer' => 'ok',
                'message' => 'Публикация опубликована' // Исправлено сообщение
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'answer' => 'error',
                'message' => 'Объект не найден'
            ], 404);
        } catch (\Exception $e) {
            Log::channel('error_file')->error('Error in publish', [
                'exception' => $e->getMessage(),
                'performer_id' => $request->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'answer' => 'error',
                'message' => 'Произошла ошибка при публикации'
            ], 500);
        }
    }
}
