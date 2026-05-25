<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Services\ProductSearchService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ProductSearch extends Component
{
    #[Url(history: true)]
    public string $search = '';

    public array $suggestions = [];
    public bool $showSuggestions = false;
    private ProductSearchService $searchService;

    public function mount()
    {
        $this->searchService = app(ProductSearchService::class);
    }

    #[On('updated:search')]
    public function updateSearch(string $value): void
    {
        $this->search = $value;

        if (strlen($this->search) < 2) {
            $this->suggestions = [];
            $this->showSuggestions = false;
            return;
        }

        // Get suggestions from Meilisearch
        try {
            $results = \App\Domains\Catalog\Models\Product::search($this->search)
                ->select(['id', 'name', 'slug'])
                ->limit(5)
                ->get();

            $this->suggestions = $results
                ->map(fn($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                ])
                ->toArray();

            $this->showSuggestions = !empty($this->suggestions);
        } catch (\Exception $e) {
            $this->suggestions = [];
            $this->showSuggestions = false;
        }
    }

    public function selectSuggestion(string $slug): void
    {
        $this->search = '';
        $this->showSuggestions = false;
        $this->dispatch('search-selected', slug: $slug);
    }

    public function performSearch(): void
    {
        if (strlen($this->search) > 0) {
            $this->showSuggestions = false;
            $this->dispatch('search-submitted', query: $this->search);
        }
    }

    public function render()
    {
        return view('livewire.catalog.product-search');
    }
}
