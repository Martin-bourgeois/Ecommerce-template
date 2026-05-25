<div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow overflow-hidden group">
    <!-- Image -->
    <div class="relative overflow-hidden bg-gray-100 h-48">
        @if ($product->image)
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        <!-- Badge -->
        @if ($product->is_featured)
            <span class="absolute top-2 right-2 bg-blue-600 text-white px-2 py-1 rounded text-xs font-semibold">★ Vedette</span>
        @endif
        @if ($product->discounted_price && $product->discounted_price < $product->price)
            <span class="absolute top-2 left-2 bg-red-600 text-white px-2 py-1 rounded text-xs font-semibold">
                -{{ round((1 - $product->discounted_price / $product->price) * 100) }}%
            </span>
        @endif
    </div>

    <!-- Content -->
    <div class="p-4">
        <!-- Name -->
        <h3 class="font-semibold text-gray-900 truncate mb-1">
            <a href="{{ route('catalog.show', $product->slug) }}" class="hover:text-blue-600">
                {{ $product->name }}
            </a>
        </h3>

        <!-- Category -->
        @if ($product?->categories?->count())
            <p class="text-xs text-gray-500 mb-3">
                {{ $product->categories->first()->name }}
            </p>
        @endif

        <!-- Rating -->
        @if ($product->ratings_count > 0)
            <div class="flex items-center mb-3">
                <div class="flex text-yellow-400">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= round($product->ratings->avg('rating') ?? 0))
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        @else
                            <svg class="w-4 h-4 fill-gray-300" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        @endif
                    @endfor
                </div>
                <span class="text-xs text-gray-600 ml-1">({{ $product->ratings_count }})</span>
            </div>
        @endif

        <!-- Price -->
        <div class="mb-4">
            @if ($product->discounted_price && $product->discounted_price < $product->price)
                <div class="flex items-center space-x-2">
                    <x-price :price="$product->discounted_price" class="text-lg font-bold text-red-600" />
                    <x-price :price="$product->price" class="text-sm text-gray-400 line-through" />
                </div>
            @else
                <x-price :price="$product->price" class="text-lg font-bold text-gray-900" />
            @endif
        </div>

        <!-- Add to Cart Button -->
        <div class="w-full">
            @livewire('product.quick-add-to-cart', ['productId' => $product->id], key('quick-add-' . $product->id))
        </div>
    </div>
</div>
