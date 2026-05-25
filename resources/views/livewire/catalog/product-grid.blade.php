<div class="space-y-6">
    <!-- Filters Sidebar -->
    <aside class="bg-white rounded-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Filtres</h3>

        <div class="space-y-6">
            <!-- Search -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Recherche</label>
                <input 
                    type="text" 
                    wire:model.live.debounce-300ms="search"
                    placeholder="Mot-clé..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <!-- Categories -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Catégorie</label>
                <select wire:model.live="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['slug'] }}">{{ $cat['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Price Range -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Gamme de prix (€)</label>
                <div class="space-y-2">
                    <input 
                        type="number" 
                        wire:model.live.debounce-300ms="minPrice"
                        placeholder="Min"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <input 
                        type="number" 
                        wire:model.live.debounce-300ms="maxPrice"
                        placeholder="Max"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>

            <!-- Sort -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Trier par</label>
                <select wire:model.live="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="newest">Plus récent</option>
                    <option value="price_asc">Prix: bas à haut</option>
                    <option value="price_desc">Prix: haut à bas</option>
                    <option value="popular">Plus vendu</option>
                    <option value="rating">Mieux noté</option>
                </select>
            </div>

            <!-- Reset -->
            <button 
                wire:click="resetFilters"
                class="w-full bg-gray-200 text-gray-900 py-2 rounded-lg hover:bg-gray-300 font-semibold"
            >
                Réinitialiser
            </button>
        </div>
    </aside>

    <!-- Products Grid -->
    <div>
        @if ($loading)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @for ($i = 0; $i < 6; $i++)
                    <div class="bg-gray-200 rounded-lg h-64 animate-pulse"></div>
                @endfor
            </div>
        @elseif ($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-lg">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun produit trouvé</h3>
                <p class="text-gray-600">Essayez d'autres filtres ou mots-clés</p>
            </div>
        @endif
    </div>
</div>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <span>Trier par :</span>
                    <select wire:change="changeSort($event.target.value)" class="border border-gray-300 rounded px-2 py-1">
                        <option value="relevance">Pertinence</option>
                        <option value="price_asc">Prix : bas à haut</option>
                        <option value="price_desc">Prix : haut à bas</option>
                        <option value="newest">Nouveauté</option>
                        <option value="bestseller">Popularité</option>
                        <option value="rating">Meilleure note</option>
                    </select>
                </label>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    @if($isLoading)
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
    @elseif(empty($products))
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Aucun produit trouvé</h3>
            <p class="mt-2 text-sm text-gray-500">Essayez de modifier vos critères de recherche ou vos filtres.</p>
        </div>
    @else
        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                    <!-- Product Image -->
                    <div class="aspect-square bg-gray-200 overflow-hidden">
                        @if($product->getFirstMediaUrl('products'))
                            <img
                                src="{{ $product->getFirstMediaUrl('products') }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover hover:scale-110 transition-transform"
                            />
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 line-clamp-2 text-sm">{{ $product->name }}</h3>

                        <!-- Rating -->
                        @if($product->rating_avg > 0)
                            <div class="mt-2 flex items-center gap-1">
                                <div class="flex text-yellow-400">
                                    @for($i = 0; $i < 5; $i++)
                                        @if($i < floor($product->rating_avg))
                                            <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @else
                                            <svg class="h-4 w-4 text-gray-300 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-xs text-gray-600">({{ round($product->rating_avg, 1) }})</span>
                            </div>
                        @endif

                        <!-- Price -->
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-lg font-semibold text-gray-900">{{ number_format($product->price / 100, 2, ',', ' ') }}€</span>
                        </div>

                        <!-- Stock Status -->
                        <div class="mt-2">
                            @if($product->in_stock)
                                <span class="inline-block px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                    En stock
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                    Rupture
                                </span>
                            @endif
                        </div>

                        <!-- Add to Cart -->
                        <button
                            class="mt-4 w-full bg-blue-600 text-white py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
                            @disabled(!$product->in_stock)
                        >
                            Ajouter au panier
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($pagination && $pagination['last_page'] > 1)
            <div class="mt-8 flex items-center justify-center gap-2">
                @if($pagination['current_page'] > 1)
                    <button
                        wire:click="changePage({{ $pagination['current_page'] - 1 }})"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Précédent
                    </button>
                @endif

                @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                    @if($i === $pagination['current_page'])
                        <span class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm font-medium">
                            {{ $i }}
                        </span>
                    @else
                        <button
                            wire:click="changePage({{ $i }})"
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            {{ $i }}
                        </button>
                    @endif
                @endfor

                @if($pagination['current_page'] < $pagination['last_page'])
                    <button
                        wire:click="changePage({{ $pagination['current_page'] + 1 }})"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Suivant
                    </button>
                @endif
            </div>
        @endif
    @endif
</div>
