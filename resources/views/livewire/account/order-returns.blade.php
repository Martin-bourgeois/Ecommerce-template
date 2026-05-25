<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Mes retours</h1>
            <p class="mt-2 text-gray-600">Gérez vos demandes de retour et suivez leur statut.</p>
        </div>

        @if ($rmas->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m-8-4v10M4 12h16" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun retour</h3>
                <p class="mt-1 text-sm text-gray-500">Vous n'avez pas de demandes de retour.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($rmas as $rma)
                    <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        Retour #{{ $rma->rma_number }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Commande #{{ $rma->order->order_number }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                                        x-data="{ color: '{{ $rma->status->color() }}' }"
                                        :style="`background-color: ${color}20; color: ${color};`">
                                        {{ $rma->status->label() }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Raison</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $rma->reason->label() }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Articles</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $rma->items->count() }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Créé</p>
                                    <p class="mt-1 text-sm font-medium text-gray-900">
                                        {{ $rma->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('account.rma.show', $rma) }}"
                                        class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                        Détails →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $rmas->links() }}
            </div>
        @endif
    </div>
</div>
