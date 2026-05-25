<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Code Promo</h2>
    </div>

    @if ($message)
        <div class="mb-4 p-4 rounded-lg text-white
            @if ($messageType === 'success') bg-green-500 @else bg-red-500 @endif">
            {{ $message }}
        </div>
    @endif

    <form wire:submit.prevent="applyCoupon" class="space-y-4">
        <div class="flex gap-2">
            <input type="text"
                   wire:model.defer="couponCode"
                   placeholder="Entrez votre code promo..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   autocomplete="off">
            <button type="submit"
                    class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                Appliquer
            </button>
        </div>
    </form>
</div>
