<div class="space-y-8">
    <!-- Product Header -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Images -->
        <div>
            <div class="bg-gray-100 rounded-lg overflow-hidden mb-4 h-96 flex items-center justify-center" id="mainImageContainer">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" id="mainImage" class="w-full h-full object-cover">
                @else
                    <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                @endif
            </div>
        </div>

        <!-- Info -->
        <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

            @if ($product->categories->count())
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach ($product->categories as $category)
                        <x-category-badge :category="$category" />
                    @endforeach
                </div>
            @endif

            <!-- Price -->
            <div class="mb-6 pb-6 border-b">
                @if ($product->discounted_price && $product->discounted_price < $product->price)
                    <div class="flex items-center space-x-3">
                        <x-price :price="$product->discounted_price" class="text-3xl font-bold text-red-600" />
                        <x-price :price="$product->price" class="text-lg text-gray-500 line-through" />
                    </div>
                @else
                    <x-price :price="$product->price" class="text-3xl font-bold text-gray-900" />
                @endif
            </div>

            <!-- Stock -->
            <div class="mb-6">
                @if ($product->stock > 0)
                    <p class="text-green-600 font-semibold">✓ En stock</p>
                @else
                    <p class="text-red-600 font-semibold">✕ En rupture</p>
                @endif
            </div>

            <!-- Quantity & Add to Cart -->
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <label class="font-semibold text-gray-900">Quantité:</label>
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button 
                            wire:click="decrementQuantity" 
                            class="px-4 py-2 hover:bg-gray-100"
                            @disabled($quantity <= 1)
                        >−</button>
                        <input 
                            type="number" 
                            wire:model="quantity" 
                            class="w-16 text-center border-0 focus:ring-0" 
                            min="1"
                            @disabled($product->stock <= 0)
                        >
                        <button 
                            wire:click="incrementQuantity" 
                            class="px-4 py-2 hover:bg-gray-100"
                            @disabled($quantity >= $product->stock)
                        >+</button>
                    </div>
                </div>

                <button 
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold @if($product->stock <= 0) opacity-50 cursor-not-allowed @endif"
                    @disabled($product->stock <= 0)
                >
                    @if ($adding)
                        <span wire:loading>Ajout en cours...</span>
                    @else
                        Ajouter au panier
                    @endif
                </button>

                @if ($message)
                    <div class="p-3 rounded-lg @if(strpos($message, 'Ajouté') !== false) bg-green-50 text-green-700 @else bg-red-50 text-red-700 @endif">
                        {{ $message }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="bg-white rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Description</h2>
        <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
    </div>
</div>
