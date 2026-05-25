<div>
    <!-- Header with Product Count and Sort -->
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Tous les produits</h1>
        @if (!empty($products))
            <p class="text-gray-600">{{ count($products) }} produit(s)</p>
        @endif
    </div>

    <!-- Loading State -->
    @if($isLoading)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @for ($i = 0; $i < 6; $i++)
                <div class="bg-gray-200 rounded-lg h-64 animate-pulse"></div>
            @endfor
        </div>
    @elseif(!empty($products))
        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>

        <!-- Pagination -->
        @if (isset($pagination) && $pagination['last_page'] > 1)
            <div class="mt-8 flex justify-center gap-2">
                @if ($pagination['current_page'] > 1)
                    <button wire:click="changePage({{ $pagination['current_page'] - 1 }})" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Précédent
                    </button>
                @endif

                @for ($i = 1; $i <= $pagination['last_page']; $i++)
                    @if ($i === $pagination['current_page'])
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">{{ $i }}</button>
                    @else
                        <button wire:click="changePage({{ $i }})" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">{{ $i }}</button>
                    @endif
                @endfor

                @if ($pagination['current_page'] < $pagination['last_page'])
                    <button wire:click="changePage({{ $pagination['current_page'] + 1 }})" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Suivant
                    </button>
                @endif
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Aucun produit trouvé</h3>
            <p class="mt-2 text-sm text-gray-500">Essayez de modifier vos critères de recherche ou vos filtres.</p>
        </div>
    @endif
</div>
