<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="mb-6 pb-6 border-b">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Commande - Étape {{ $step }} / 3</h2>
        <div class="flex items-center space-x-2">
            @for ($i = 1; $i <= 3; $i++)
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold
                        @if($i < $step) bg-green-600 text-white
                        @elseif($i === $step) bg-blue-600 text-white
                        @else bg-gray-300 text-gray-600
                        @endif">
                        @if($i < $step) ✓ @else {{ $i }} @endif
                    </div>
                    @if($i < 3)
                        <div class="w-12 h-1 @if($i < $step) bg-green-600 @else bg-gray-300 @endif"></div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <!-- Step 1: Shipping Address -->
    @if ($step === 1)
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Adresse de livraison</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Prénom</label>
                    <input type="text" wire:model="firstName" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('firstName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nom</label>
                    <input type="text" wire:model="lastName" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('lastName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Téléphone</label>
                    <input type="tel" wire:model="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Rue</label>
                    <input type="text" wire:model="street" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('street') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Ville</label>
                    <input type="text" wire:model="city" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('city') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Code postal</label>
                    <input type="text" wire:model="postalCode" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('postalCode') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Pays</label>
                    <input type="text" wire:model="country" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @error('country') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    @endif

    <!-- Step 2: Shipping & Payment -->
    @if ($step === 2)
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Méthode de livraison</h3>
                <div class="space-y-3">
                    <label class="flex items-center border border-gray-300 rounded-lg p-4 cursor-pointer @if($shippingMethod === 'standard') bg-blue-50 border-blue-500 @endif">
                        <input type="radio" wire:model="shippingMethod" value="standard" class="mr-3">
                        <div>
                            <p class="font-semibold text-gray-900">Livraison standard</p>
                            <p class="text-sm text-gray-600">5-7 jours ouvrables</p>
                        </div>
                    </label>

                    <label class="flex items-center border border-gray-300 rounded-lg p-4 cursor-pointer @if($shippingMethod === 'express') bg-blue-50 border-blue-500 @endif">
                        <input type="radio" wire:model="shippingMethod" value="express" class="mr-3">
                        <div>
                            <p class="font-semibold text-gray-900">Livraison express</p>
                            <p class="text-sm text-gray-600">2-3 jours ouvrables</p>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Méthode de paiement</h3>
                <div class="space-y-3">
                    <label class="flex items-center border border-gray-300 rounded-lg p-4 cursor-pointer @if($paymentMethod === 'card') bg-blue-50 border-blue-500 @endif">
                        <input type="radio" wire:model="paymentMethod" value="card" class="mr-3">
                        <p class="font-semibold text-gray-900">Carte bancaire</p>
                    </label>

                    <label class="flex items-center border border-gray-300 rounded-lg p-4 cursor-pointer @if($paymentMethod === 'paypal') bg-blue-50 border-blue-500 @endif">
                        <input type="radio" wire:model="paymentMethod" value="paypal" class="mr-3">
                        <p class="font-semibold text-gray-900">PayPal</p>
                    </label>

                    <label class="flex items-center border border-gray-300 rounded-lg p-4 cursor-pointer @if($paymentMethod === 'bank') bg-blue-50 border-blue-500 @endif">
                        <input type="radio" wire:model="paymentMethod" value="bank" class="mr-3">
                        <p class="font-semibold text-gray-900">Virement bancaire</p>
                    </label>
                </div>
            </div>
        </div>
    @endif

    <!-- Step 3: Review -->
    @if ($step === 3)
        <div class="space-y-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Livraison à</h4>
                <p class="text-gray-600">{{ $firstName }} {{ $lastName }}</p>
                <p class="text-gray-600">{{ $street }}, {{ $postalCode }} {{ $city }}, {{ $country }}</p>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Articles</h3>
                <div class="space-y-2">
                    @foreach ($cartItems as $item)
                        <div class="flex justify-between text-gray-900">
                            <span>{{ $item['product_name'] }} x{{ $item['quantity'] }}</span>
                            <x-price :price="$item['total']" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t pt-4 space-y-2">
                <div class="flex justify-between text-gray-600">
                    <span>Sous-total</span>
                    <x-price :price="$subtotal" />
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>TVA (20%)</span>
                    <x-price :price="$tax" />
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Livraison</span>
                    @if ($shipping > 0)
                        <x-price :price="$shipping" />
                    @else
                        <span class="text-green-600 font-semibold">Gratuite</span>
                    @endif
                </div>
                <div class="flex justify-between text-xl font-bold text-gray-900 pt-2 border-t">
                    <span>Total</span>
                    <x-price :price="$total" />
                </div>
            </div>
        </div>
    @endif

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8 pt-6 border-t">
        <button 
            @if($step === 1) disabled @else wire:click="previousStep" @endif
            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold @if($step === 1) opacity-50 cursor-not-allowed @endif">
            ← Précédent
        </button>

        @if ($step < 3)
            <button 
                wire:click="nextStep"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                Suivant →
            </button>
        @else
            <button 
                wire:click="submit"
                wire:loading.attr="disabled"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                @if ($processing)
                    <span wire:loading>Traitement...</span>
                @else
                    Confirmer la commande
                @endif
            </button>
        @endif
    </div>

    @if ($message)
        <div class="mt-4 p-3 rounded-lg @if(strpos($message, 'Erreur') !== false) bg-red-50 text-red-700 @else bg-green-50 text-green-700 @endif">
            {{ $message }}
        </div>
    @endif
</div>
