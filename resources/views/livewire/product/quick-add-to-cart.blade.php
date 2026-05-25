<div class="relative">
    <button 
        wire:click="addToCart"
        wire:loading.attr="disabled"
        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <span wire:loading.remove>Ajouter au panier</span>
        <span wire:loading>
            <svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>

    @if ($showMessage)
        <div class="absolute top-full left-0 right-0 mt-2 p-2 rounded text-sm text-center z-10 @if(strpos($message, '✓') !== false) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
            {{ $message }}
        </div>
    @endif
</div>
