<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Services\ProductSearchService;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Computed;

class ProductFilters extends Component
{
    #[Url(history: true)]
    public array $categories = [];

    #[Url(history: true)]
    public ?int $price_min = null;

    #[Url(history: true)]
    public ?int $price_max = null;

    #[Url(history: true)]
    public ?int $rating = null;

    #[Url(history: true)]
    public string $availability = 'all';

    #[Url(history: true)]
    public array $selectedAttributes = [];

    public array $filterOptions = [];
    public int $resultCount = 0;

    private ProductSearchService $searchService;

    public function mount()
    {
        $this->searchService = app(ProductSearchService::class);
        $this->loadFilterOptions();
    }

    public function loadFilterOptions(): void
    {
        $this->filterOptions = $this->searchService->getFilterOptions();
    }

    public function updateFilters(): void
    {
        // URL parameters are automatically updated by Livewire
        $this->dispatch('filters-updated', filters: $this->getFiltersArray());
    }

    public function updatedCategories(): void
    {
        $this->updateFilters();
    }

    public function updatedPrice_min(): void
    {
        $this->updateFilters();
    }

    public function updatedPrice_max(): void
    {
        $this->updateFilters();
    }

    public function updatedRating(): void
    {
        $this->updateFilters();
    }

    public function updatedAvailability(): void
    {
        $this->updateFilters();
    }

    public function updatedAttributes(): void
    {
        $this->updateFilters();
    }

    public function resetFilters(): void
    {
        $this->categories = [];
        $this->price_min = null;
        $this->price_max = null;
        $this->rating = null;
        $this->availability = 'all';
        $this->selectedAttributes = [];

        $this->dispatch('filters-updated', filters: []);
    }

    public function toggleAttribute(string $attributeSlug, string $optionValue): void
    {
        if (!isset($this->selectedAttributes[$attributeSlug])) {
            $this->selectedAttributes[$attributeSlug] = [];
        }

        $key = array_search($optionValue, $this->selectedAttributes[$attributeSlug]);
        if ($key !== false) {
            unset($this->selectedAttributes[$attributeSlug][$key]);
        } else {
            $this->selectedAttributes[$attributeSlug][] = $optionValue;
        }

        // Clean up empty arrays
        $this->selectedAttributes = array_filter($this->selectedAttributes);

        $this->updateFilters();
    }

    public function getFiltersArray(): array
    {
        $filters = [];

        if (!empty($this->categories)) {
            $filters['category'] = $this->categories;
        }

        if ($this->price_min || $this->price_max) {
            $filters['price'] = [
                'min' => $this->price_min,
                'max' => $this->price_max,
            ];
        }

        if ($this->rating) {
            $filters['rating'] = $this->rating;
        }

        if ($this->availability !== 'all') {
            $filters['availability'] = $this->availability;
        }

        if (!empty($this->selectedAttributes)) {
            $filters['attributes'] = $this->selectedAttributes;
        }

        return $filters;
    }

    public function render()
    {
        return view('livewire.catalog.product-filters', [
            'filterOptions' => $this->filterOptions,
        ]);
    }
}
