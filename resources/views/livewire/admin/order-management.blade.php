<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Gestion des Commandes</h2>

    <!-- Filters -->
    <div class="mb-6 flex gap-4">
        <div class="flex-1">
            <input type="text"
                   wire:model.live="searchQuery"
                   placeholder="Chercher par numéro ou email..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <select wire:model.live="statusFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Tous les statuts</option>
            @foreach ($orderStatuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <!-- Orders Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Numéro</th>
                    <th class="px-4 py-3 text-left font-semibold">Client</th>
                    <th class="px-4 py-3 text-left font-semibold">Statut</th>
                    <th class="px-4 py-3 text-right font-semibold">Total</th>
                    <th class="px-4 py-3 text-left font-semibold">Créée</th>
                    <th class="px-4 py-3 text-center font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">
                            <div>
                                <p class="font-medium">{{ $order->user->name }}</p>
                                <p class="text-xs text-gray-600">{{ $order->user->email }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                @switch($order->status->color())
                                    @case('yellow') bg-yellow-100 text-yellow-800 @break
                                    @case('blue') bg-blue-100 text-blue-800 @break
                                    @case('purple') bg-purple-100 text-purple-800 @break
                                    @case('green') bg-green-100 text-green-800 @break
                                    @case('red') bg-red-100 text-red-800 @break
                                    @case('orange') bg-orange-100 text-orange-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">
                                {{ $order->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $order->getTotalFormatted() }}</td>
                        <td class="px-4 py-3 text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <button wire:click="editOrder({{ $order->id }})"
                                    class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded transition">
                                ✎ Éditer
                            </button>
                            @if ($order->canBeCancelled)
                                <button wire:click="cancelOrder({{ $order->id }})"
                                        class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition"
                                        onclick="return confirm('Êtes-vous sûr ?')">
                                    ✕ Annuler
                                </button>
                            @endif
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    @if ($editingOrderId === $order->id)
                        <tr class="bg-blue-50 border-2 border-blue-300">
                            <td colspan="6" class="px-4 py-4">
                                <div class="max-w-xl">
                                    <h3 class="font-semibold text-gray-900 mb-4">Changer le statut</h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Nouveau statut
                                            </label>
                                            <select wire:model="newStatus"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                @foreach ($order->getAllowedTransitions() as $status)
                                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Raison (optionnel)
                                            </label>
                                            <textarea wire:model="statusReason"
                                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                      rows="3"
                                                      placeholder="Ex: Délai de paiement excédé..."></textarea>
                                        </div>

                                        <div class="flex gap-2">
                                            <button wire:click="updateStatus"
                                                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded transition">
                                                Confirmer
                                            </button>
                                            <button wire:click="closeEdit"
                                                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 rounded transition">
                                                Annuler
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-600">
                            Aucune commande trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
