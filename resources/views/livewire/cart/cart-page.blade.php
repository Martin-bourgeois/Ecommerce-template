<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Cart Items -->
    <div class="lg:col-span-2">
        @if (count($items) > 0)
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Mon Panier</h2>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Produit</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Quantité</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-900">Prix unitaire</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-900">Total</th>
                                <th class="text-center py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="border-b hover:bg-gray-50">
                                    <!-- Product -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-4">
                                            @if ($item['product_image'])
                                                <img src="{{ asset('storage/' . $item['product_image']) }}" alt="{{ $item['product_name'] }}" class="w-16 h-16 object-cover rounded">
                                            @else
                                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('catalog.show', $item['product_slug']) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                                                    {{ $item['product_name'] }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Quantity -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex items-center justify-center border border-gray-300 rounded inline-flex">
                                            <button 
                                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                                class="px-2 py-1 hover:bg-gray-100"
                                            >−</button>
                                            <span class="px-4 py-1">{{ $item['quantity'] }}</span>
                                            <button 
                                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                                class="px-2 py-1 hover:bg-gray-100"
                                            >+</button>
                                        </div>
                                    </td>

                                    <!-- Unit Price -->
                                    <td class="py-4 px-4 text-right">
                                        <x-price :price="$item['price']" />
                                    </td>

                                    <!-- Total -->
                                    <td class="py-4 px-4 text-right font-semibold">
                                        <x-price :price="$item['total']" />
                                    </td>

                                    <!-- Remove -->
                                    <td class="py-4 px-4 text-center">
                                        <button 
                                            wire:click="removeItem({{ $item['id'] }})"
                                            class="text-red-600 hover:text-red-700 font-semibold"
                                        >
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Coupon -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Code promo</h3>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        wire:model="couponCode"
                        placeholder="Entrez votre code promo"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <button 
                        wire:click="applyCoupon"
                        wire:loading.attr="disabled"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700"
                    >
                        Appliquer
                    </button>
                </div>

                @if ($couponError)
                    <p class="text-red-600 text-sm mt-2">{{ $couponError }}</p>
                @endif

                @if ($couponMessage)
                    <p class="text-green-600 text-sm mt-2">{{ $couponMessage }}</p>
                @endif
            </div>
        @else
            <!-- Empty Cart -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Panier vide</h3>
                <p class="text-gray-600 mb-6">Vous n'avez pas encore ajouté de produits</p>
                <a href="{{ route('catalog.index') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Commencer le shopping
                </a>
            </div>
        @endif
    </div>

    <!-- Summary -->
    @if (count($items) > 0)
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-20">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Récapitulatif</h3>

                <div class="space-y-3 mb-4 pb-4 border-b">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total</span>
                        <x-price :price="$totals['subtotal']" />
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>TVA (20%)</span>
                        <x-price :price="$totals['tax']" />
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Livraison</span>
                        @if ($totals['shipping'] > 0)
                            <x-price :price="$totals['shipping']" />
                        @else
                            <span class="text-green-600 font-semibold">Gratuite</span>
                        @endif
                    </div>
                </div>

                @if ($totals['discount'] > 0)
                    <div class="flex justify-between text-green-600 font-semibold mb-4 pb-4 border-b">
                        <span>Réduction</span>
                        <span>-<x-price :price="$totals['discount']" /></span>
                    </div>
                @endif

                <div class="flex justify-between text-xl font-bold text-gray-900 mb-6">
                    <span>Total</span>
                    <x-price :price="$totals['total']" />
                </div>

                <button 
                    wire:click="checkout"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold"
                >
                    Passer la commande
                </button>

                <a href="{{ route('catalog.index') }}" class="block text-center mt-4 text-blue-600 hover:text-blue-700 font-semibold">
                    Continuer le shopping
                </a>
            </div>
        </div>
    @endif
</div>
