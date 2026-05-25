<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Mes commandes</h2>
        
        <select wire:model.live="filter" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="all">Toutes</option>
            <option value="pending">En attente</option>
            <option value="completed">Complétées</option>
        </select>
    </div>

    @if ($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Numéro</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Statut</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-900">Total</th>
                        <th class="text-center py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $order->order_number }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($order->status->value === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status->value === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($order->status->value) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-gray-900">
                                <x-price :price="$order->total" />
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('account.order-detail', $order->id) }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-600">Vous n'avez pas encore de commandes</p>
        </div>
    @endif
</div>
