<?php

declare(strict_types=1);

namespace App\Http\Livewire\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Enums\ProductStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ProductGrid extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public ?float $minPrice = null;

    #[Url]
    public ?float $maxPrice = null;

    #[Url]
    public string $sort = 'newest';

    public array $categories = [];
    public bool $loading = false;

    public function mount(): void
    {
        $this->loadCategories();
    }

    private function loadCategories(): void
    {
        $this->categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->toArray();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function render()
    {
        $this->loading = true;

        $query = Product::query()->where('is_active', true);

        // Filtre par catégorie
        if ($this->category) {
            $query->whereHas('categories', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        // Filtre par prix
        if ($this->minPrice !== null) {
            $query->where('price', '>=', $this->minPrice);
        }
        if ($this->maxPrice !== null) {
            $query->where('price', '<=', $this->maxPrice);
        }

        // Recherche
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                  ->orWhere('description', 'ilike', "%{$this->search}%");
            });
        }

        // Tri
        $query = match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->withCount('orderItems as sales_count')->orderByDesc('sales_count'),
            'rating' => $query->withCount('ratings as avg_rating')->orderByDesc('avg_rating'),
            default => $query->latest(),
        };

        $products = $query->paginate(12);
        $this->loading = false;

        return view('livewire.catalog.product-grid', [
            'products' => $products,
        ]);
    }
}
