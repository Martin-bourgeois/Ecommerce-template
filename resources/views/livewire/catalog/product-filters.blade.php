<div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Filtres</h3>
        @if(!empty($categories) || $price_min || $price_max || $rating || $availability !== 'all' || !empty($selectedAttributes))
            <button wire:click="resetFilters" class="text-sm text-blue-600 hover:text-blue-700">
                Réinitialiser
            </button>
        @endif
    </div>

    <!-- Category Filter -->
    <div class="mb-6">
        <h4 class="font-medium text-gray-900 mb-3">Catégorie</h4>
        <div class="space-y-2">
            @foreach($filterOptions['categories'] ?? [] as $cat)
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        wire:model.live="categories"
                        value="{{ $cat['value'] }}"
                        class="rounded border-gray-300"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ $cat['label'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Price Range Filter -->
    <div class="mb-6">
        <h4 class="font-medium text-gray-900 mb-3">Prix</h4>
        <div class="flex items-center gap-2">
            <input
                type="number"
                wire:model.live="price_min"
                placeholder="Min"
                class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
            />
            <span class="text-gray-400">-</span>
            <input
                type="number"
                wire:model.live="price_max"
                placeholder="Max"
                class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
            />
            <span class="text-xs text-gray-500">€</span>
        </div>
        <div class="text-xs text-gray-500 mt-2">
            @if($filterOptions['price_range']['min'] && $filterOptions['price_range']['max'])
                {{ $filterOptions['price_range']['min'] }}€ - {{ $filterOptions['price_range']['max'] }}€
            @endif
        </div>
    </div>

    <!-- Availability Filter -->
    <div class="mb-6">
        <h4 class="font-medium text-gray-900 mb-3">Disponibilité</h4>
        <div class="space-y-2">
            @foreach($filterOptions['availability'] ?? [] as $avail)
                <label class="flex items-center">
                    <input
                        type="radio"
                        wire:model.live="availability"
                        value="{{ $avail['value'] }}"
                        class="border-gray-300"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ $avail['label'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Rating Filter -->
    <div class="mb-6">
        <h4 class="font-medium text-gray-900 mb-3">Évaluation</h4>
        <div class="space-y-2">
            @foreach($filterOptions['ratings'] ?? [] as $rate)
                <label class="flex items-center">
                    <input
                        type="radio"
                        wire:model.live="rating"
                        value="{{ $rate['value'] }}"
                        class="border-gray-300"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ $rate['label'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Attribute Filters (Dynamic) -->
    @foreach($filterOptions['attributes'] ?? [] as $attribute)
        <div class="mb-6">
            <h4 class="font-medium text-gray-900 mb-3">{{ $attribute['name'] }}</h4>
            <div class="space-y-2">
                @foreach($attribute['options'] ?? [] as $option)
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            wire:click="toggleAttribute('{{ $attribute['slug'] }}', '{{ $option['value'] }}')"
                            @checked(in_array($option['value'], $selectedAttributes[$attribute['slug']] ?? []))
                            class="rounded border-gray-300"
                        />
                        <span class="ml-2 text-sm text-gray-700">{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
