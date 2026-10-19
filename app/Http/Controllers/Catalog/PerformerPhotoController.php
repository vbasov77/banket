<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Performer;
use App\Models\PerformerPhoto;
use App\Services\PerformerPhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PerformerPhotoController extends Controller
{
    private const MAX_PHOTOS = 10;

    /**
     * @param PerformerPhotoService $service
     */
    public function __construct(
        private readonly PerformerPhotoService $service,
    )
    {
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        $performer = $this->service->findOwnedByUser($request->id, $request->user()->id);
        $performer->load('photos');

        return view('catalog.request.photos', [
            'performer' => $performer,]);
    }

    /**
     * @param Request $request
     * @param Performer $performer
     * @return JsonResponse
     */
    public function store(Request $request, Performer $performer): JsonResponse
    {
        $this->authorizeOwner($performer);

        if (!$request->hasFile('img')) {
            return response()->json(['message' => 'Файл изображения не предоставлен'], 400);
        }

        $count = $performer->photos()->count();
        if ($count >= self::MAX_PHOTOS) {
            return response()->json([
                'message' => "Максимум " . self::MAX_PHOTOS . " фото. Сейчас загружено: {$count}.",
            ], 422);
        }

        $request->validate([
            'img' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
        ]);

        $data = $this->service->store($request, $performer);

        return response()->json($data + ['message' => 'Изображение успешно загружено'], 201);
    }

    /**
     * @param Performer $performer
     * @param PerformerPhoto $photo
     * @return JsonResponse
     */
    public function destroy(Performer $performer, PerformerPhoto $photo): JsonResponse
    {
        $this->authorizeOwner($performer);

        abort_unless($photo->performer_id === $performer->id, 404);

        $this->service->delete($photo);

        return response()->json(['answer' => 'ok']);
    }

    /**
     * @param Request $request
     * @param Performer $performer
     * @return JsonResponse
     */
    public function changeOrder(Request $request, Performer $performer): JsonResponse
    {
        $this->authorizeOwner($performer);

        $ids = $request->validate([
            'order' => ['required', 'array', 'max:' . self::MAX_PHOTOS],
            'order.*' => ['integer'],
        ])['order'];

        $this->service->changeOrder($ids);

        return response()->json(['message' => 'Порядок сохранён']);
    }

    /**
     * @param Performer $performer
     * @return void
     */
    private function authorizeOwner(Performer $performer): void
    {
        abort_unless($performer->user_id === auth()->id(), 403, 'Нет прав на эту карточку');
    }
}
