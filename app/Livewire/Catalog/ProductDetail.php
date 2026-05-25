<?php

declare(strict_types=1);

namespace App\Livewire\Catalog;

use Livewire\Component;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Repositories\ProductRepository;

class ProductDetail extends Component
{
    public Product $product;

    public ?ProductVariant $selectedVariant = null;

    public array $selectedAttributes = [];

    public int $quantity = 1;

    protected $repository;

    public function mount(string $slug)
    {
        $this->repository = app(ProductRepository::class);
        $this->product = $this->repository->findBySlug($slug) ?? abort(404);

        if ($this->product->variants->isNotEmpty()) {
            $this->selectedVariant = $this->product->variants->first();
            $this->loadSelectedAttributes();
        }

        $this->product->incrementViewCount();
    }

    public function updatedSelectedAttributes()
    {
        if ($this->product->type->isConfigurable()) {
            $this->findMatchingVariant();
        }
    }

    public function addToCart()
    {
        if (!$this->selectedVariant) {
            $this->dispatch('notification', 'Veuillez sélectionner une variante');
            return;
        }

        if ($this->selectedVariant->getAvailableStock() < $this->quantity) {
            $this->dispatch('notification', 'Stock insuffisant');
            return;
        }

        $this->dispatch('cart:add', [
            'variant_id' => $this->selectedVariant->id,
            'quantity' => $this->quantity,
        ]);

        $this->dispatch('notification', 'Produit ajouté au panier');
    }

    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function increaseQuantity()
    {
        if ($this->selectedVariant && $this->quantity < $this->selectedVariant->getAvailableStock()) {
            $this->quantity++;
        }
    }

    public function render()
    {
        $attributes = $this->product->variants()
            ->with(['attributeValues' => fn ($q) => $q->with(['attribute', 'attributeOption'])])
            ->active()
            ->get()
            ->flatMap(fn ($v) => $v->attributeValues)
            ->groupBy('attribute.slug')
            ->map(fn ($values) => $values->unique('attribute_option_id'));

        return view('livewire.catalog.product-detail', [
            'attributes' => $attributes,
            'selectedVariantPrice' => $this->selectedVariant?->price,
            'selectedVariantStock' => $this->selectedVariant?->getAvailableStock(),
        ]);
    }

    private function loadSelectedAttributes(): void
    {
        if ($this->selectedVariant) {
            $this->selectedAttributes = $this->selectedVariant
                ->attributeValues()
                ->with(['attribute', 'attributeOption'])
                ->get()
                ->mapWithKeys(fn ($av) => [$av->attribute->slug => $av->attribute_option_id])
                ->toArray();
        }
    }

    private function findMatchingVariant(): void
    {
        $variant = $this->product->variants()
            ->active()
            ->whereHas('attributeValues', function ($query) {
                foreach ($this->selectedAttributes as $slug => $optionId) {
                    $query->orWhereHas('attribute', fn ($q) => $q->where('slug', $slug))
                        ->where('attribute_option_id', $optionId);
                }
            }, '=', count($this->selectedAttributes))
            ->first();

        if ($variant) {
            $this->selectedVariant = $variant;
            $this->quantity = 1;
        }
    }
}
