<div class="ticket-queue bg-white rounded-lg p-6">
    <!-- Header avec Stats -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-4">Queue Support</h2>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 text-sm">Total</p>
                <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-amber-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 text-sm">Ouverts</p>
                <p class="text-2xl font-bold">{{ $stats['open'] }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 text-sm">SLA dépassés</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 text-sm">Non assignés</p>
                <p class="text-2xl font-bold">{{ $stats['unassigned'] }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 text-sm">Résolus</p>
                <p class="text-2xl font-bold">{{ $stats['by_status']['resolved'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="flex gap-2 mb-6">
        <button
            wire:click="setFilter('assigned_to_me')"
            class="px-4 py-2 rounded {{ $filter === 'assigned_to_me' ? 'bg-blue-600 text-white' : 'bg-gray-100' }}"
        >
            Mes Assignations
        </button>
        <button
            wire:click="setFilter('unassigned')"
            class="px-4 py-2 rounded {{ $filter === 'unassigned' ? 'bg-blue-600 text-white' : 'bg-gray-100' }}"
        >
            Non Assignés
        </button>
        <button
            wire:click="setFilter('all')"
            class="px-4 py-2 rounded {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100' }}"
        >
            Tous
        </button>
    </div>

    <!-- Liste -->
    @if ($tickets->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold">#ID</th>
                        <th class="text-left px-4 py-3 font-semibold">Sujet</th>
                        <th class="text-left px-4 py-3 font-semibold">Client</th>
                        <th class="text-left px-4 py-3 font-semibold">Statut</th>
                        <th class="text-center px-4 py-3 font-semibold">Priorité</th>
                        <th class="text-center px-4 py-3 font-semibold">SLA</th>
                        <th class="text-center px-4 py-3 font-semibold">Assigné</th>
                        <th class="text-center px-4 py-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.support.ticket.show', $ticket) }}" class="text-blue-600 hover:underline">
                                    {{ Str::limit($ticket->subject, 30) }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $ticket->client->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-{{ $ticket->status->color() }}-100 text-{{ $ticket->status->color() }}-800">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-{{ $ticket->priority->color() }}-100 text-{{ $ticket->priority->color() }}-800">
                                    {{ $ticket->priority->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                @if ($ticket->isOverdue())
                                    <span class="text-red-600 font-semibold">⚠️ Dépassé</span>
                                @else
                                    <span class="text-gray-600">{{ number_format($ticket->slaRemainingHours(), 1) }}h</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                @if ($ticket->assignedTo)
                                    {{ Str::limit($ticket->assignedTo->name, 15) }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.support.ticket.show', $ticket) }}" class="text-blue-600 hover:underline text-sm">
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
            {{ $tickets->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">
                @if ($filter === 'unassigned')
                    Tous les tickets sont assignés! ✓
                @elseif ($filter === 'assigned_to_me')
                    Aucun ticket assigné.
                @else
                    Aucun ticket ouvert.
                @endif
            </p>
        </div>
    @endif
</div>
