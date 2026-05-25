<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Domains\Catalog\Models\Category;
use Illuminate\View\View;

class CategoryComposer
{
    /**
     * Bind data to the view
     */
    public function compose(View $view): void
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        $view->with('allCategories', $categories);
    }
}
