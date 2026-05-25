<div class="ticket-detail bg-white rounded-lg p-6">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6 pb-6 border-b">
        <div>
            <h1 class="text-3xl font-bold mb-2">#{{ $ticket->id }} - {{ $ticket->subject }}</h1>
            <p class="text-gray-600">Créé le {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="text-right">
            <span class="inline-block px-4 py-2 rounded-full font-semibold bg-{{ $ticket->status->color() }}-100 text-{{ $ticket->status->color() }}-800">
                {{ $ticket->status->label() }}
            </span>
            <span class="inline-block px-4 py-2 rounded-full font-semibold bg-{{ $ticket->priority->color() }}-100 text-{{ $ticket->priority->color() }}-800 ml-2">
                {{ $ticket->priority->label() }}
            </span>
        </div>
    </div>

    <!-- Infos -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 pb-6 border-b">
        <div>
            <p class="text-gray-600 text-sm">Catégorie</p>
            <p class="font-semibold">{{ $ticket->category->label() }}</p>
        </div>
        <div>
            <p class="text-gray-600 text-sm">Priorité</p>
            <p class="font-semibold">{{ $ticket->priority->label() }}</p>
        </div>
        @if ($ticket->assignedTo)
            <div>
                <p class="text-gray-600 text-sm">Assigné à</p>
                <p class="font-semibold">{{ $ticket->assignedTo->name }}</p>
            </div>
        @endif
        @if ($ticket->order)
            <div>
                <p class="text-gray-600 text-sm">Commande</p>
                <a href="{{ route('orders.show', $ticket->order) }}" class="font-semibold text-blue-600 hover:underline">
                    #{{ $ticket->order->id }}
                </a>
            </div>
        @endif
    </div>

    <!-- Messages -->
    <div class="mb-6">
        <h3 class="text-xl font-bold mb-4">Conversation</h3>

        <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
            @foreach ($messages as $message)
                <div class="border rounded-lg p-4 {{ $message->is_internal ? 'bg-yellow-50 border-yellow-200' : '' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold">
                                {{ $message->author->name }}
                                @if ($message->isFromStaff())
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded ml-2">Support</span>
                                @endif
                                @if ($message->is_internal)
                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-2">Interne</span>
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

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            Envoyer
        </button>
    </form>
</div>
