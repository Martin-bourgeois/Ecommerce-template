<div class="relative">
    <div class="flex items-center gap-2 bg-white rounded-lg border border-gray-300 px-4 py-2 shadow-sm">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <input
            type="text"
            wire:model.debounce-300ms="search"
            wire:keydown.enter="performSearch"
            placeholder="Rechercher des produits..."
            class="flex-1 outline-none text-sm"
        />
    </div>

    @if($showSuggestions && !empty($suggestions))
        <div class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-300 rounded-lg shadow-lg z-10">
            @foreach($suggestions as $suggestion)
                <button
                    wire:click="selectSuggestion('{{ $suggestion['slug'] }}')"
                    class="w-full text-left px-4 py-2 hover:bg-gray-100 border-b border-gray-200 last:border-b-0"
                >
                    <div class="font-medium text-gray-900">{{ $suggestion['name'] }}</div>
                </button>
            @endforeach
        </div>
    @endif
</div>
