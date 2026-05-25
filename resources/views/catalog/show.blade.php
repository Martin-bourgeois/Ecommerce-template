@extends('layouts.app')

@section('title', $product->name . ' - ' . config('app.name'))
@section('breadcrumbs')
    <span>/</span>
    <a href="{{ route('catalog.index') }}" class="hover:text-gray-900">Catalogue</a>
    <span>/</span>
    @foreach ($product->categories as $category)
        <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="hover:text-gray-900">{{ $category->name }}</a>
    @endforeach
    <span>/</span>
    <span class="text-gray-900">{{ $product->name }}</span>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
    <!-- Image Gallery -->
    <div>
        <div class="bg-gray-100 rounded-lg overflow-hidden mb-4 h-96 flex items-center justify-center">
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover" id="mainImage">
            @else
                <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            @endif
        </div>

        <!-- Thumbnails -->
        @if ($product->images && $product->images->count())
            <div class="grid grid-cols-5 gap-2">
                @foreach ($product->images as $image)
                    <button class="thumbnail-btn border-2 border-gray-300 rounded hover:border-blue-600" data-image="{{ asset('storage/' . $image->path) }}">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="Thumbnail" class="w-full h-20 object-cover">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Product Info -->
    <div>
        <!-- Name -->
        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>

        <!-- Categories -->
        @if ($product->categories->count())
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($product->categories as $category)
                    <x-category-badge :category="$category" />
                @endforeach
            </div>
        @endif

        <!-- Rating -->
        <div class="flex items-center mb-6 pb-6 border-b">
            @if ($reviewStats['total_reviews'] > 0)
                <div class="flex text-yellow-400 mr-2">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= round($reviewStats['average_rating']))
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        @else
                            <svg class="w-5 h-5 fill-gray-300" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        @endif
                    @endfor
                </div>
                <span class="text-gray-600 ml-2">({{ $reviewStats['total_reviews'] }} avis)</span>
            @endif
        </div>

        <!-- Price -->
        <div class="mb-6">
            @if ($product->discounted_price && $product->discounted_price < $product->price)
                <div class="flex items-center space-x-3 mb-2">
                    <x-price :price="$product->discounted_price" class="text-3xl font-bold text-red-600" />
                    <x-price :price="$product->price" class="text-lg text-gray-500 line-through" />
                    <span class="bg-red-600 text-white px-3 py-1 rounded text-sm font-bold">
                        -{{ round((1 - $product->discounted_price / $product->price) * 100) }}%
                    </span>
                </div>
            @else
                <x-price :price="$product->price" class="text-3xl font-bold text-gray-900" />
            @endif
        </div>

        <!-- Description -->
        <div class="mb-8 pb-8 border-b">
            <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
        </div>

        <!-- Stock Status -->
        <div class="mb-8">
            @if ($product->stock > 0)
                <p class="text-green-600 font-semibold mb-4">✓ En stock ({{ $product->stock }} disponible{{ $product->stock > 1 ? 's' : '' }})</p>
            @else
                <p class="text-red-600 font-semibold mb-4">✕ En rupture de stock</p>
            @endif
        </div>

        <!-- Add to Cart -->
        @livewire('catalog.add-to-cart', ['product' => $product])

        <!-- Meta Info -->
        <div class="border-t pt-6 space-y-2 text-sm text-gray-600">
            <p><strong>SKU:</strong> {{ $product->sku }}</p>
            <p><strong>Catégorie:</strong> {{ $product->categories->pluck('name')->join(', ') }}</p>
            <p><strong>Référence:</strong> {{ $product->id }}</p>
        </div>
    </div>
</div>

<!-- Related Products -->
@if ($relatedProducts->count())
    <section class="mb-12 border-t pt-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Produits similaires</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($relatedProducts as $related)
                <x-product-card :product="$related" />
            @endforeach
        </div>
    </section>
@endif

<!-- Reviews Section -->
@if ($product->reviews->count() || auth()->check())
    <section class="border-t pt-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Avis clients</h2>

        <!-- Stats -->
        @if ($reviewStats['total_reviews'] > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
                <!-- Average Rating -->
                <div class="text-center">
                    <div class="text-6xl font-bold text-gray-900 mb-2">
                        {{ number_format($reviewStats['average_rating'], 1, ',', '') }}
                    </div>
                    <div class="flex justify-center text-yellow-400 mb-2">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= round($reviewStats['average_rating']))
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                            @else
                                <svg class="w-5 h-5 fill-gray-300" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                            @endif
                        @endfor
                    </div>
                    <p class="text-gray-600">basé sur {{ $reviewStats['total_reviews'] }} avis</p>
                </div>

                <!-- Rating Breakdown -->
                <div class="space-y-3" x-data="{ percentages: {{ json_encode(collect(range(1, 5))->reverse()->mapWithKeys(fn($s) => [$s => $reviewStats['total_reviews'] > 0 ? ($reviewStats['by_stars'][$s] / $reviewStats['total_reviews'] * 100) : 0])->toArray()) }} }">
                    @for ($stars = 5; $stars >= 1; $stars--)
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600 w-12">{{ $stars }} ★</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-400 h-2 rounded-full" x-bind:style="{ width: percentages[{{ $stars }}] + '%' }"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12 text-right">{{ $reviewStats['by_stars'][$stars] }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        @endif

        <!-- Reviews List -->
        @if ($product->reviews->count())
            <div class="space-y-6 mb-8">
                @foreach ($product->reviews->take(5) as $review)
                    <div class="border-b pb-6">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $review->author_name ?? 'Anonyme' }}</p>
                                <p class="text-sm text-gray-500">{{ $review->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex text-yellow-400">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $review->rating)
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    @else
                                        <svg class="w-4 h-4 fill-gray-300" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $review->title }}</h3>
                        <p class="text-gray-700">{{ $review->content }}</p>
                    </div>
                @endforeach
            </div>

            @if ($product->reviews->count() > 5)
                <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Voir tous les avis →</a>
            @endif
        @endif

        <!-- Add Review CTA -->
        @auth
            @if (!Auth::user()->hasReviewedProduct($product->id))
                <div class="bg-blue-50 rounded-lg p-6 mt-8">
                    <h3 class="font-bold text-gray-900 mb-2">Vous avez acheté ce produit?</h3>
                    <p class="text-gray-700 mb-4">Partagez votre avis pour aider les autres clients</p>
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Laisser un avis</button>
                </div>
            @endif
        @else
            <div class="bg-blue-50 rounded-lg p-6 mt-8">
                <p class="text-gray-700 mb-4">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Connectez-vous</a>
                    pour laisser un avis
                </p>
            </div>
        @endauth
    </section>
@endif

<script>
    // Handle thumbnail clicks
    document.querySelectorAll('.thumbnail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
                mainImage.src = this.dataset.image;
            }
        });
    });
</script>
@endsection
