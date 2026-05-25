<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Services\ProductSearchService;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class ProductGrid extends Component
{
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'relevance';

    #[Url(history: true)]
    public int $page = 1;

    public array $filters = [];
    public array $results = [];
    public array $pagination = [];
    public bool $isLoading = false;

    private ProductSearchService $searchService;

    public function mount()
    {
        $this->searchService = app(ProductSearchService::class);
        $this->loadProducts();
    }

    #[On('filters-updated')]
    #[On('search-submitted')]
    public function updateSearch(array $filters = [], string $query = ''): void
    {
        $this->filters = $filters;
        $this->search = $query ?: $this->search;
        $this->page = 1;
        $this->loadProducts();
    }

    public function changePage(int $page): void
    {
        $this->page = $page;
        $this->loadProducts();
    }

    public function changeSort(string $sort): void
    {
        $this->sort = $sort;
        $this->page = 1;
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $this->isLoading = true;

        try {
            $response = $this->searchService->search(
                query: $this->search ?: null,
                filters: $this->filters,
                sort: $this->sort,
                perPage: 24,
                page: $this->page,
            );

            $this->results = $response['data'];
            $this->pagination = $response['pagination'];
        } catch (\Exception $e) {
            Log::error('Product search error', ['error' => $e->getMessage()]);
            $this->results = [];
            $this->pagination = [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.catalog.product-grid', [
            'products' => $this->results,
            'pagination' => $this->pagination,
            'isLoading' => $this->isLoading,
            'sort' => $this->sort,
        ]);
    }
}
