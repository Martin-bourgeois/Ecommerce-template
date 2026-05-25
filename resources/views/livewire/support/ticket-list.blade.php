<div class="support-tickets bg-white rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Mes Tickets</h2>

    <!-- Filtres -->
    <div class="flex gap-2 mb-6 flex-wrap">
        <button
            wire:click="setFilter(null)"
            class="px-4 py-2 rounded {{ is_null($filterStatus) ? 'bg-blue-600 text-white' : 'bg-gray-100' }}"
        >
            Tous
        </button>
        @foreach ($statuses as $status)
            <button
                wire:click="setFilter(@json($status))"
                class="px-4 py-2 rounded {{ $filterStatus === $status ? 'bg-' . $status->color() . '-600 text-white' : 'bg-gray-100' }}"
            >
                {{ $status->label() }}
            </button>
        @endforeach
    </div>

    <!-- Liste -->
    @if ($tickets->count() > 0)
        <div class="space-y-3">
            @foreach ($tickets as $ticket)
                <a href="{{ route('support.ticket.show', $ticket) }}" class="block border rounded-lg p-4 hover:shadow-lg transition">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-bold text-lg">#{{ $ticket->id }} - {{ $ticket->subject }}</h3>
                            <p class="text-sm text-gray-600">{{ $ticket->category->label() }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-{{ $ticket->status->color() }}-100 text-{{ $ticket->status->color() }}-800">
                                {{ $ticket->status->label() }}
                            </span>
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-{{ $ticket->priority->color() }}-100 text-{{ $ticket->priority->color() }}-800 ml-2">
                                {{ $ticket->priority->label() }}
                            </span>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500">
                        Créé le {{ $ticket->created_at->format('d/m/Y H:i') }}
                        @if ($ticket->assignedTo)
                            • Assigné à {{ $ticket->assignedTo->name }}
                        @endif
                    </p>

                    @if ($ticket->messages->count() > 0)
                        <p class="text-sm text-gray-500 mt-2">
                            {{ $ticket->messages->count() }} réponse{{ $ticket->messages->count() > 1 ? 's' : '' }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Aucun ticket trouvé.</p>
        </div>
    @endif
</div>
