<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Mes Commandes</h2>

    @forelse ($orders as $order)
        <div class="border border-gray-200 rounded-lg p-4 mb-4 hover:shadow-md transition">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $order->order_number }}</h3>
                    <p class="text-sm text-gray-600">
                        Créée le {{ $order->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">
                        {{ $order->getTotalFormatted() }}
                    </p>
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
                </div>
            </div>

            <div class="mb-4 text-sm text-gray-600">
                <p>{{ $order->items->count() }} article(s)</p>
            </div>

            <a href="{{ route('account.orders.show', $order) }}"
               class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition">
                Détails →
            </a>
        </div>
    @empty
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <p class="text-gray-600">Aucune commande pour le moment</p>
        </div>
    @endforelse

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
