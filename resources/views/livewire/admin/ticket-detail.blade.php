<div class="ticket-detail bg-white rounded-lg p-6">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6 pb-6 border-b">
        <div class="flex-1">
            <h1 class="text-3xl font-bold mb-2">#{{ $ticket->id }} - {{ $ticket->subject }}</h1>
            <p class="text-gray-600 mb-3">Créé le {{ $ticket->created_at->format('d/m/Y H:i') }} par <strong>{{ $ticket->client->name }}</strong></p>

            @if ($ticket->order)
                <p class="text-sm text-gray-600">
                    Commande associée:
                    <a href="{{ route('orders.show', $ticket->order) }}" class="text-blue-600 hover:underline">
                        #{{ $ticket->order->id }}
                    </a>
                </p>
            @endif
        </div>
        <div class="text-right">
            <span class="inline-block px-4 py-2 rounded-full font-semibold bg-{{ $ticket->status->color() }}-100 text-{{ $ticket->status->color() }}-800 mb-2">
                {{ $ticket->status->label() }}
            </span>
            <span class="inline-block px-4 py-2 rounded-full font-semibold bg-{{ $ticket->priority->color() }}-100 text-{{ $ticket->priority->color() }}-800">
                {{ $ticket->priority->label() }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6 mb-6">
        <!-- Infos -->
        <div>
            <h3 class="font-bold mb-3 border-b pb-2">Informations</h3>

            <div class="mb-4">
                <p class="text-gray-600 text-sm">Catégorie</p>
                <p class="font-semibold">{{ $ticket->category->label() }}</p>
            </div>

            <div class="mb-4">
                <p class="text-gray-600 text-sm">Priorité</p>
                <p class="font-semibold">{{ $ticket->priority->label() }}</p>
            </div>

            <div>
                <p class="text-gray-600 text-sm">SLA</p>
                @if ($ticket->isOverdue())
                    <p class="text-red-600 font-semibold">⚠️ Dépassé</p>
                @else
                    <p class="font-semibold">{{ number_format($ticket->slaRemainingHours(), 1) }}h restantes</p>
                @endif
            </div>
        </div>

        <!-- Statut -->
        <div>
            <h3 class="font-bold mb-3 border-b pb-2">Changer le Statut</h3>

            <div class="space-y-2">
                @foreach ($statuses as $status)
                    <button
                        wire:click="changeStatus(@json($status))"
                        class="block w-full text-left px-3 py-2 rounded hover:bg-gray-100 transition"
                    >
                        {{ $status->label() }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Assignation -->
        <div>
            <h3 class="font-bold mb-3 border-b pb-2">Assignation</h3>

            @if ($ticket->assignedTo)
                <p class="mb-3">
                    <strong>Assigné à:</strong><br>
                    {{ $ticket->assignedTo->name }}
                </p>
            @else
                <p class="mb-3 text-amber-600 font-semibold">Non assigné</p>
            @endif

            <div class="space-y-2">
                <select
                    wire:model="selectedStaffId"
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Sélectionner un staff...</option>
                    @foreach ($staffMembers as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>

                <button
                    wire:click="assignTo($wire.entangle('selectedStaffId').live)"
                    class="w-full px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                >
                    Assigner
                </button>

                @if ($ticket->assignedTo)
                    <button
                        wire:click="reassign"
                        class="w-full px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition text-sm"
                    >
                        Auto-Réassigner
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div class="mb-6 border-t pt-6">
        <h3 class="text-xl font-bold mb-4">Conversation</h3>

        <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
            @foreach ($messages as $message)
                <div class="border rounded-lg p-4 {{ $message->is_internal ? 'bg-yellow-50 border-yellow-300' : 'bg-gray-50' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold">
                                {{ $message->author->name }}
                                @if ($message->isFromStaff())
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded ml-2">Support</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded ml-2">Client</span>
                                @endif
                                @if ($message->is_internal)
                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-2">🔒 Interne</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <p class="text-gray-800 whitespace-pre-line">{{ $message->content }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Form Réponse -->
    <form wire:submit="reply" class="border-t pt-6">
        <div class="mb-4">
            <label class="block font-semibold mb-2">Votre réponse</label>
            <textarea
                wire:model="newMessage"
                rows="5"
                placeholder="Écrivez votre message..."
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                maxlength="5000"
            ></textarea>
            <p class="text-sm text-gray-500 mt-1">{{ strlen($newMessage) }}/5000 caractères</p>
            @error('newMessage') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4 flex items-center gap-3">
            <label class="flex items-center">
                <input
                    type="checkbox"
                    wire:model="isInternal"
                    class="rounded"
                >
                <span class="ml-2 text-sm">Message interne (non visible au client)</span>
            </label>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            Envoyer
        </button>
    </form>
</div>
