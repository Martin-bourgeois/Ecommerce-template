<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Confirmations de Paiement</h2>
        <p class="text-gray-600 text-sm mt-1">Confirmez les paiements manuels reçus</p>
    </div>

    @if ($payments->isEmpty())
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <p class="text-gray-600">✓ Aucun paiement en attente</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-700">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Référence</th>
                        <th class="px-4 py-3 text-left font-semibold">Commande</th>
                        <th class="px-4 py-3 text-left font-semibold">Client</th>
                        <th class="px-4 py-3 text-right font-semibold">Montant</th>
                        <th class="px-4 py-3 text-left font-semibold">Créé</th>
                        <th class="px-4 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($payments as $payment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ substr($payment->reference, 0, 20) }}...
                            </td>
                            <td class="px-4 py-3">
                                <a href="#" class="text-blue-600 hover:underline">
                                    #{{ $payment->payable_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @if ($payment->payable)
                                    {{ $payment->payable->user->name ?? 'N/A' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                {{ $payment->getFormattedAmount() }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $payment->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center space-x-2">
                                <button
                                    wire:click="confirm({{ $payment->id }})"
                                    class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded transition"
                                >
                                    ✓ Confirmer
                                </button>
                                <button
                                    wire:click="reject({{ $payment->id }})"
                                    class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition"
                                >
                                    ✕ Rejeter
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($payments->hasPages())
            <div class="mt-6">
                {{ $payments->links() }}
            </div>
        @endif
    @endif
</div>
