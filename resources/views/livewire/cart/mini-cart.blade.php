<div class="relative" x-data="{ open: false }">
    <!-- Cart Icon -->
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        @if ($cartCount > 0)
            <span class="absolute top-1 right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $cartCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl p-4 z-50">
        @if (count($items) > 0)
            <h3 class="font-bold text-gray-900 mb-4">Mon panier</h3>

            <!-- Items -->
            <div class="space-y-3 max-h-96 overflow-y-auto mb-4 border-b pb-4">
                @foreach ($items as $item)
                    <div class="flex items-start space-x-3">
                        <!-- Image -->
                        @if ($item['product_image'])
                            <img src="{{ asset('storage/' . $item['product_image']) }}" alt="{{ $item['product_name'] }}" 
                                 class="w-12 h-12 object-cover rounded">
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-gray-900 truncate">{{ $item['product_name'] }}</p>
                            <p class="text-xs text-gray-600">{{ $item['quantity'] }}x <x-price :price="$item['price']" /></p>
                            <p class="font-semibold text-sm text-gray-900"><x-price :price="$item['total']" /></p>
                        </div>

                        <!-- Remove -->
                        <button wire:click="removeItem({{ $item['id'] }})" class="text-red-600 hover:text-red-700 text-sm font-semibold">
                            ✕
                        </button>
                    </div>
                @endforeach
            </div>

            <!-- Total -->
            <div class="mb-4 pb-4 border-b">
                <div class="flex justify-between font-bold text-gray-900">
                    <span>Total:</span>
                    <x-price :price="$total" />
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-2">
                <a href="{{ route('cart.index') }}" class="block w-full text-center bg-gray-100 text-gray-900 py-2 rounded hover:bg-gray-200">
                    Voir le panier
                </a>
                <a href="{{ route('checkout.index') }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Commander
                </a>
            </div>
        @else
            <p class="text-gray-600 text-center py-8">Panier vide</p>
        @endif
    </div>
</div>
