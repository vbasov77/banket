<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\CatalogCategory;
use App\Models\Performer;
use App\Services\PerformerPackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;


class PerformerPackageController extends Controller
{
    public function __construct(
        protected PerformerPackageService $service,
    ) {}

    /**
     * @param CatalogCategory $category
     * @param Performer $performer
     * @return View
     */
    public function edit(CatalogCategory $category, Performer $performer): View
    {
        try {
            $this->authorizeOwner($performer);

            // пакеты из базы приводим к массиву того же вида, что и old()
            $packageItems = old('packages', $performer->packages->map(fn ($p) => [
                'title'       => $p->title,
                'description' => $p->description,
                'price_min'   => $p->price_min,
                'price_max'   => $p->price_max,
                'is_addon'    => $p->is_addon,
            ])->all());

            if ($packageItems === []) {
                $packageItems = [['title' => '', 'description' => '', 'price_min' => '', 'price_max' => '', 'is_addon' => false]];
            }

            return view('catalog.packages.packages', [
                'category'     => $category,
                'performer'    => $performer,
                'packageItems' => $packageItems,
            ]);
        } catch (HttpException $e) {
            throw $e; // осознанные abort'ы (403 от authorizeOwner, 404 от биндинга) — отрисует фреймворк
        } catch (\Throwable $e) {
            Log::channel('error_file')->error('Packages edit error', [
                'message'      => $e->getMessage(),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
                'category_id'  => $category->getKey(),
                'performer_id' => $performer->getKey(),
                'user_id'      => auth()->id(),
                'exception'    => $e,
            ]);

            return $this->handleError($e);
        }
    }

    /**
     * @param Request $request
     * @param CatalogCategory $category
     * @param Performer $performer
     * @return RedirectResponse
     */
    public function update(Request $request, CatalogCategory $category, Performer $performer): RedirectResponse
    {
        $this->authorizeOwner($performer);

        try {
            $this->service->syncForPerformer($performer->id, $request->input('packages'));
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }


        return redirect()
            ->route('catalog.packages.edit', [$category->slug, $performer->id])
            ->with('success', 'Пакеты услуг сохранены');
    }

    /**
     *  Владелец или админ — подгоните под вашу проверку прав
     *  (на страницах субъекта у вас используется $isAuthorOrAdmin).
     * @param Performer $performer
     * @return void
     */
    protected function authorizeOwner(Performer $performer): void
    {
        $user = auth()->user();

        abort_if($user === null, 403);
        abort_if($performer->user_id !== $user->id && !$user->is_admin, 403);
    }
}
