<?php

declare(strict_types=1);

namespace App\Http\Livewire\Layout;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Debounce;

class SearchBar extends Component
{
    public string $query = '';

    public array $results = [];
    public bool $showResults = false;

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            $this->showResults = false;
            return;
        }

        $this->showResults = true;
        $this->searchProducts();
    }

    private function searchProducts(): void
    {
        // Produits
        $products = Product::where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->query}%")
                  ->orWhere('description', 'ilike', "%{$this->query}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'slug', 'image', 'price', 'discounted_price'])
            ->map(fn($p) => [
                'type' => 'product',
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->discounted_price ?? $p->price,
                'image' => $p->image,
            ])
            ->toArray();

        // Catégories
        $categories = Category::where('is_active', true)
            ->where('name', 'ilike', "%{$this->query}%")
            ->limit(3)
            ->get(['name', 'slug'])
            ->map(fn($c) => [
                'type' => 'category',
                'name' => $c->name,
                'slug' => $c->slug,
            ])
            ->toArray();

        $this->results = array_merge($products, $categories);
    }

    public function selectResult(string $type, string $slug): void
    {
        $this->showResults = false;
        $this->query = '';

        if ($type === 'product') {
            $this->redirectRoute('catalog.show', $slug);
        } else {
            $this->redirectRoute('catalog.index', ['category' => $slug]);
        }
    }

    public function render()
    {
        return view('livewire.layout.search-bar', [
            'results' => $this->results,
        ]);
    }
}
