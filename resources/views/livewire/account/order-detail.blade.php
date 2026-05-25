<div class="space-y-6">
    <!-- Message Alert -->
    @if ($message)
        <div class="rounded-lg p-4 
            @if ($messageType === 'success') bg-green-50 border border-green-200 text-green-800
            @else bg-red-50 border border-red-200 text-red-800
            @endif">
            {{ $message }}
        </div>
    @endif

    <!-- Order Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-sm text-gray-600">Numéro de commande</p>
                <p class="text-2xl font-bold text-gray-900">{{ $orderData['order_number'] ?? 'N/A' }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                @if($orderData['status']->value === 'pending_payment') bg-yellow-100 text-yellow-800
                @elseif($orderData['status']->value === 'completed') bg-green-100 text-green-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst(str_replace('_', ' ', $orderData['status']->value ?? 'unknown')) }}
            </span>
        </div>
        <p class="text-gray-600">{{ $orderData['created_at'] ?? 'N/A' }}</p>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Articles</h3>

        @if (count($items) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Produit</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-900">Quantité</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900">Prix unitaire</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-900">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="border-b">
                                <td class="py-3 px-4 text-gray-900 font-semibold">{{ $item['product_name'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-600">{{ $item['qty'] }}</td>
                                <td class="py-3 px-4 text-right text-gray-600"><x-price :price="$item['price_cents'] / 100" /></td>
                                <td class="py-3 px-4 text-right text-gray-900 font-semibold"><x-price :price="$item['total_cents'] / 100" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600 text-center py-8">Aucun article</p>
        @endif
    </div>

    <!-- Total -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center text-xl font-bold text-gray-900">
            <span>Total de la commande</span>
            <x-price :price="$orderData['total_cents'] / 100" class="text-2xl" />
        </div>
    </div>

    <!-- PayPal Payment Section (if payment_method is PayPal) -->
    @if ($order->payment_method === 'paypal')
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-sm p-6 border border-blue-200">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9.5 6.5c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm0 4c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm0 4c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2z"/>
                </svg>
                <h3 class="text-lg font-bold text-blue-900">Détails du Paiement PayPal</h3>
            </div>

            <div class="bg-white rounded-lg p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- PayPal Account Email -->
                    <div>
                        <p class="text-xs font-semibold text-gray-600 mb-2">COMPTE PAYPAL</p>
                        <p class="text-sm text-gray-900 font-medium">{{ config('services.paypal.email') }}</p>
                    </div>

                    <!-- Amount Due -->
                    <div>
                        <p class="text-xs font-semibold text-gray-600 mb-2">MONTANT À PAYER</p>
                        <p class="text-sm text-gray-900 font-bold"><x-price :price="$orderData['total_cents'] / 100" /></p>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="mb-4">
                @if ($orderData['payment_verified_at'])
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <p class="text-sm text-green-800">
                            ✓ Paiement confirmé le {{ \Carbon\Carbon::parse($orderData['payment_verified_at'])->format('d/m/Y H:i') }}
                        </p>
                        @if ($orderData['paypal_proof_path'])
                            <a wire:click="downloadPaypalProof" class="text-green-600 hover:text-green-700 text-sm font-semibold mt-2 cursor-pointer">
                                ↓ Télécharger la preuve
                            </a>
                        @endif
                    </div>
                @else
                    <!-- Upload Proof Form -->
                    <form wire:submit="uploadPaypalProof" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                ID de Transaction PayPal *
                            </label>
                            <input 
                                type="text" 
                                wire:model="paypalTransactionId"
                                placeholder="Ex: 1AB23456789WXYZ"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            @error('paypalTransactionId')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Preuve de Paiement (PDF, JPG, PNG) *
                            </label>
                            <input 
                                type="file" 
                                wire:model="paypalProof"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <p class="text-xs text-gray-600 mt-1">Taille maximale: 5 MB</p>
                            @error('paypalProof')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-2 rounded-lg font-semibold transition-colors"
                        >
                            <span wire:loading.remove>📤 Télécharger la preuve</span>
                            <span wire:loading>Envoi en cours...</span>
                        </button>
                    </form>
                @endif
            </div>

            <!-- Instructions -->
            <div class="bg-blue-100 rounded-lg p-3 text-sm text-blue-900">
                <p class="font-semibold mb-2">📝 Instructions:</p>
                <ol class="list-decimal list-inside space-y-1 text-xs">
                    <li>Effectuez le paiement sur votre compte PayPal vers <strong>{{ config('services.paypal.email') }}</strong></li>
                    <li>Montant exact: <strong><x-price :price="$orderData['total_cents'] / 100" /></strong></li>
                    <li>Prenez une capture d'écran ou un PDF de la confirmation de transaction</li>
                    <li>Remplissez l'ID de transaction et téléchargez la preuve ci-dessus</li>
                    <li>Notre équipe validera le paiement sous 24 heures</li>
                </ol>
            </div>
        </div>
    @endif

    <!-- Back Button -->
    <div>
        <a href="{{ route('account.orders') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
            ← Retour aux commandes
        </a>
    </div>
</div>

