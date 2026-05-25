<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Gestion des retours</h1>
            <p class="mt-2 text-gray-600">Gérez les demandes de retour et les remboursements.</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">En attente</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Approuvés</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">En inspection</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['inspected'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Remboursés</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total_refunded'], 0) }}€</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200">
                <div class="flex">
                    <button @click="$wire.activeTab = 'pending'"
                        :class="activeTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                        En attente ({{ $stats['pending'] }})
                    </button>
                    <button @click="$wire.activeTab = 'receipt'"
                        :class="activeTab === 'receipt' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                        En attente de réception ({{ $stats['approved'] }})
                    </button>
                    <button @click="$wire.activeTab = 'inspection'"
                        :class="activeTab === 'inspection' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                        En inspection ({{ $stats['inspected'] }})
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                @forelse($rmas as $rma)
                    <div class="border rounded-lg p-6 mb-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $rma->rma_number }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ $rma->user->name }} - Commande #{{ $rma->order->order_number }}
                                </p>
                            </div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                x-data="{ color: '{{ $rma->status->color() }}' }"
                                :style="`background-color: ${color}20; color: ${color};`">
                                {{ $rma->status->label() }}
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="mb-4 bg-gray-50 rounded p-4">
                            <p class="text-sm font-medium text-gray-900 mb-3">Articles:</p>
                            @foreach ($rma->items as $item)
                                <div class="text-sm text-gray-600">
                                    {{ $item->orderItem->product->name }} (x{{ $item->quantity }})
                                </div>
                            @endforeach
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 flex-wrap">
                            @if ($rma->status->value === 0)
                                <button wire:click="approveRma({{ $rma->id }})"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Approuver
                                </button>
                                <button wire:click="rejectRma({{ $rma->id }}, 'Rejété par l\'administrateur')"
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                    Rejeter
                                </button>
                            @elseif ($rma->status->value === 1)
                                <button wire:click="receiveRma({{ $rma->id }})"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    Marquer comme reçu
                                </button>
                            @elseif ($rma->status->value === 2)
                                <div class="w-full">
                                    <p class="text-sm font-medium text-gray-900 mb-2">Condition des articles:</p>
                                    @foreach ($rma->items as $item)
                                        <div class="mb-3">
                                            <label class="block text-sm text-gray-600 mb-1">
                                                {{ $item->orderItem->product->name }}
                                            </label>
                                            <select wire:model="itemConditions.{{ $item->order_item_id }}"
                                                class="w-full text-sm border border-gray-300 rounded px-3 py-2">
                                                <option value="">-- Sélectionner --</option>
                                                <option value="unopened">Neuf (scellé)</option>
                                                <option value="opened">Ouvert</option>
                                                <option value="damaged">Endommagé</option>
                                            </select>
                                        </div>
                                    @endforeach
                                    <button wire:click="inspectRma({{ $rma->id }})"
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        Valider l'inspection
                                    </button>
                                </div>
                            @elseif ($rma->status->value === 3)
                                <button wire:click="refundRma({{ $rma->id }})"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    Traiter le remboursement
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500">Aucun RMA trouvé.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
