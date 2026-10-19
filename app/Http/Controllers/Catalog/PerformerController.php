<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\CatalogCategory;
use App\Models\Performer;
use App\Services\ImgBanPerformerService;
use App\Services\PerformerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class PerformerController extends Controller
{
    public function __construct(private PerformerService $performerService,
    private ImgBanPerformerService $imgBanPerformerService)
    {
    }


    /**
     * @param Request $request
     * @param CatalogCategory $category
     * @return View|Response
     * @throws \HttpException
     */
    public function index(Request $request, CatalogCategory $category): View|Response
    {
        try {
            abort_unless($category->published, 404, 'Категория не найдена');
            $performers = $this->performerService->list($category, $request->city);

            $metaDescription = $this->performerService->findIndexMetaDescription($category, $performers);

            return view('catalog.index', [
                'category' => $category,
                'data' => $performers,
                'metaDescription' => $metaDescription,
            ]);
        } catch (\HttpException|NotFoundHttpException $e) {
            throw $e; // осознанные abort'ы — Laravel отрисует 404/500 сам
        } catch (Throwable $e) {
            return $this->handleError($e);
        }
    }

    /**
     * @param CatalogCategory $category
     * @param int $id
     * @return View|Response
     */
    public function show(CatalogCategory $category, int $id): View|Response
    {
        try {
            $performer = $this->performerService->card($category, $id);
        } catch (HttpException $e) {
            throw $e; // 404/403 и т.п. — обрабатывает фреймворк
        } catch (Throwable $e) {
            Log::channel('error_file')->error('Card error', [
                'category_id' => $category->getKey(),
                'performer_id' => $id,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return $this->handleError($e);
        }
        $metaDescription = $this->performerService->findMetaDescription($category, $performer);

        return view('catalog.show', [
            'category' => $category,
            'data' => $performer,
            'metaDescription' => $metaDescription,
        ]);
    }

    /**
     * @param Throwable $e
     * @return Response
     */
    private function handleError(Throwable $e): Response
    {
        Log::channel('error_file')->error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl(),
            'user_id' => auth()->id(),
        ]);

        return response()->view('errors.500', [], 500);
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function deleteCategoryConfirm(Request $request): View|RedirectResponse
    {
        $id = (int)$request->id;
        $cat = Performer::findOrFail($id);

        // Проверка прав: владелец или админ
        if (!$cat->isAuthor()) {
            return redirect()->route('unauthorized')->with(['error' => 'Нет прав на удаление']);
        }

        return view('catalog.delete_confirm', [
            'category' => $cat,
            'confirmText' => "Удалить категорию id{$id}",
        ]);
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteCategory(Request $request): RedirectResponse
    {

        $id = $request->id;
        $cat = Performer::findOrFail($id);
        if (!$cat->isAuthor()) {
            return redirect()->route('unauthorized')->with([
                'error' => 'У вас нет прав для удаления этого объекта',
            ]);
        }

        // Проверка: текст из поля должен совпадать с "Удалить объект id{id}"
        $expectedText = "Удалить категорию id{$id}";


        $confirmInput = $request->input('confirm_input', '');

        if (trim($confirmInput) !== $expectedText) {
            return redirect()->back()
                ->withErrors(['confirm_input' => 'Текст подтверждения не совпадает'])
                ->withInput();
        }

        // Доп. проверка: ID из скрытого поля должен совпадать с ID из URL
        $confirmId = (int)$request->input('confirm_id', 0);
        if ($confirmId !== (int)$id) {
            Log::channel('error_file')->warning(
                'ID mismatch on delete: url_id=' . $id . ', form_id=' . $confirmId,
                ['user_id' => Auth::id()]
            );
            return redirect()->back()
                ->withErrors(['confirm_input' => 'Ошибка проверки данных'])
                ->withInput();
        }

        try {
            $objName = $cat->name;

            // Сначала чистим фото на хостинге
            $this->imgBanPerformerService->destroyPerformerImages($cat->id);

            $cat->delete();

            return redirect()->route('catalog.request.my_category')->with([
                'message' => "Объект «{$objName}» удалён",
            ]);
        } catch (\Exception $e) {
            Log::channel('error_file')->error(
                'Ошибка при удалении объекта: ' . $e->getMessage(),
                ['obj_id' => $id, 'user_id' => Auth::id()]
            );

            return redirect()->back()
                ->withErrors(['error' => 'Не удалось удалить объект'])
                ->withInput();
        }

    }


}
