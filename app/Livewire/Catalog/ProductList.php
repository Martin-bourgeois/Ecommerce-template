<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Domains\Catalog\Repositories\ProductRepository;
use App\Domains\Catalog\Models\Category;

class ProductList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $categoryId = null;

    #[Url]
    public string $sortBy = 'newest';

    public int $perPage = 12;

    protected $repository;

    public function mount()
    {
        $this->repository = app(ProductRepository::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->getProductsQuery();

        $products = $query->paginate($this->perPage);
        $categories = Category::root()->active()->with('children')->get();

        return view('livewire.catalog.product-list', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    private function getProductsQuery()
    {
        $query = \App\Domains\Catalog\Models\Product::active()
            ->with(['variants', 'category']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('short_description', 'ilike', "%{$this->search}%")
                    ->orWhere('sku', 'ilike', "%{$this->search}%");
            });
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        return match ($this->sortBy) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('view_count', 'desc'),
            default => $query->orderBy('created_at', 'desc'), // newest
        };
    }
}
