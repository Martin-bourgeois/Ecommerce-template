<div class="relative w-full" x-data="{ open: false }">
    <div class="relative">
        <input 
            type="text"
            wire:model.live="query"
            @focus="open = true"
            @click.outside="open = false"
            placeholder="Rechercher des produits..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
        <button class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>

    <!-- Results Dropdown -->
    @if ($showResults && count($results) > 0)
        <div class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
            @foreach ($results as $result)
                <button 
                    wire:click="selectResult('{{ $result['type'] }}', '{{ $result['slug'] }}')"
                    class="w-full flex items-start space-x-3 px-4 py-3 hover:bg-gray-50 border-b last:border-b-0"
                >
                    @if ($result['type'] === 'product')
                        @if (isset($result['image']))
                            <img src="{{ asset('storage/' . $result['image']) }}" alt="{{ $result['name'] }}" 
                                 class="w-10 h-10 object-cover rounded">
                        @else
                            <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 text-left">
                            <p class="font-semibold text-gray-900">{{ $result['name'] }}</p>
                            <p class="text-sm text-gray-600"><x-price :price="$result['price']" /></p>
                        </div>
                    @else
                        <div class="flex-1 text-left">
                            <p class="font-semibold text-gray-900 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 000-2H7zM4 7a1 1 0 011-1h10a1 1 0 011 1v3a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"></path>
                                </svg>
                                <span>{{ $result['name'] }}</span>
                            </p>
                            <p class="text-xs text-gray-500">Catégorie</p>
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    @elseif ($showResults && strlen($query) >= 2)
        <div class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-lg z-50 p-4 text-center text-gray-600">
            Aucun résultat pour "{{ $query }}"
        </div>
    @endif
</div>
