<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('account.returns') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                ← Retour aux retours
            </a>
            <h1 class="mt-4 text-3xl font-bold text-gray-900">Retour #{{ $rma->rma_number }}</h1>
        </div>

        <!-- Status Badge -->
        <div class="mb-8">
            <div class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium"
                x-data="{ color: '{{ $rma->status->color() }}' }"
                :style="`background-color: ${color}20; color: ${color};`">
                {{ $rma->status->label() }}
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Progression</h2>
            <div class="space-y-4">
                @php
                    $timeline = [
                        ['name' => 'Demandé', 'date' => $rma->created_at, 'status' => true],
                        ['name' => 'Approuvé', 'date' => $rma->status->value >= 1 ? $rma->updated_at : null, 'status' => $rma->status->value >= 1],
                        ['name' => 'Réceptionné', 'date' => $rma->status->value >= 2 ? $rma->updated_at : null, 'status' => $rma->status->value >= 2],
                        ['name' => 'Inspecté', 'date' => $rma->status->value >= 3 ? $rma->updated_at : null, 'status' => $rma->status->value >= 3],
                        ['name' => 'Finalisé', 'date' => $rma->status->value >= 4 ? $rma->updated_at : null, 'status' => $rma->status->value >= 4],
                    ];
                @endphp

                @foreach ($timeline as $item)
                    <div class="flex items-start">
                        <div
                            class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center {{ $item['status'] ? 'bg-green-100' : 'bg-gray-100' }}">
                            @if ($item['status'])
                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            @else
                                <span class="text-gray-400">{{ $loop->index + 1 }}</span>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium {{ $item['status'] ? 'text-gray-900' : 'text-gray-500' }}">
                                {{ $item['name'] }}
                            </p>
                            @if ($item['date'])
                                <p class="text-sm text-gray-500">{{ $item['date']->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Items -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Articles retournés</h2>
            <div class="space-y-4">
                @foreach ($rma->items as $item)
                    <div class="flex items-center justify-between border-b pb-4">
                        <div class="flex items-center flex-1">
                            @if ($item->orderItem->product->image)
                                <img src="{{ $item->orderItem->product->image }}" alt=""
                                    class="h-16 w-16 rounded object-cover">
                            @else
                                <div class="h-16 w-16 rounded bg-gray-100"></div>
                            @endif
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $item->orderItem->product->name }}
                                </p>
                                <p class="text-sm text-gray-500">Quantité: {{ $item->quantity }}</p>
                                @if ($item->condition)
                                    <p class="text-sm text-gray-500">Condition: {{ $item->condition->label() }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                {{ number_format($item->refund_amount, 2) }}€
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Résumé</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Raison du retour</span>
                    <span class="font-medium">{{ $rma->reason->label() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Montant estimé</span>
                    <span class="font-medium">{{ number_format($rma->getTotalRefundAmount(), 2) }}€</span>
                </div>
                @if ($rma->refund_amount)
                    <div class="border-t pt-3 flex justify-between">
                        <span class="text-gray-900 font-medium">Montant remboursé</span>
                        <span class="font-semibold text-green-600">{{ number_format($rma->refund_amount, 2) }}€</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if ($rma->customer_notes || $rma->admin_notes)
            <div class="bg-white rounded-lg shadow p-6 mt-8">
                @if ($rma->customer_notes)
                    <div class="mb-6">
                        <h3 class="font-medium text-gray-900">Vos notes</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $rma->customer_notes }}</p>
                    </div>
                @endif

                @if ($rma->admin_notes)
                    <div>
                        <h3 class="font-medium text-gray-900">Notes de l'administrateur</h3>
                        <p class="mt-2 text-sm text-gray-600 whitespace-pre-line">{{ $rma->admin_notes }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
