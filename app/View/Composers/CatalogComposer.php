<?php

namespace App\View\Composers;

use App\Models\CatalogCategory;
use Illuminate\View\View;

class CatalogComposer
{
    public function compose(View $view): void
    {
        $view->with('catalogCategories', CatalogCategory::query()
            ->where('published', 1)
            ->orderBy('position')
            ->get(['id', 'slug', 'name'])
        );
    }
}
