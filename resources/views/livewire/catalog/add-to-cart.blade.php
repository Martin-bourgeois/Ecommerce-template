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
        <div class="p-3 rounded-lg @if(strpos($message, 'succès') !== false) bg-green-50 text-green-700 @else bg-red-50 text-red-700 @endif">
            {{ $message }}
        </div>
    @endif
</div>
