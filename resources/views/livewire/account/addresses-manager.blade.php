<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Mes adresses</h2>
        <button 
            wire:click="openForm"
            @if($showForm) style="display:none" @endif
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold"
        >
            + Ajouter une adresse
        </button>
    </div>

    <!-- Form -->
    @if ($showForm)
        <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingId ? 'Modifier l\'adresse' : 'Nouvelle adresse' }}</h3>

            <form wire:submit.prevent="save" class="space-y-4">
                <!-- Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Type</label>
                    <select wire:model="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="shipping">Livraison</option>
                        <option value="billing">Facturation</option>
                    </select>
                </div>

                <!-- Street -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Rue</label>
                    <input type="text" wire:model="street" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- City -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Ville</label>
                    <input type="text" wire:model="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Postal Code -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Code postal</label>
                    <input type="text" wire:model="postalCode" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Country -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Pays</label>
                    <input type="text" wire:model="country" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Is Default -->
                <div class="flex items-center">
                    <input type="checkbox" wire:model="isDefault" id="isDefault" class="h-4 w-4">
                    <label for="isDefault" class="ml-2 text-sm text-gray-900">Adresse par défaut</label>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-semibold">
                        Enregistrer
                    </button>
                    <button type="button" wire:click="closeForm" class="flex-1 bg-gray-300 text-gray-900 py-2 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- List -->
    @if ($addresses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($addresses as $address)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                            @if($address->type === 'shipping') bg-blue-100 text-blue-800 @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($address->type) }}
                        </span>
                        @if($address->is_default)
                            <span class="text-xs font-semibold text-green-600">Par défaut</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-900 font-semibold">{{ $address->street }}</p>
                    <p class="text-sm text-gray-600">{{ $address->postal_code }} {{ $address->city }}</p>
                    <p class="text-sm text-gray-600">{{ $address->country }}</p>
                    
                    <div class="flex gap-2 mt-4">
                        <button wire:click="$set('editingId', {{ $address->id }}); openForm()" class="text-sm text-blue-600 hover:text-blue-700 font-semibold">
                            Modifier
                        </button>
                        <button wire:click="delete({{ $address->id }})" class="text-sm text-red-600 hover:text-red-700 font-semibold">
                            Supprimer
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $addresses->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-600 mb-4">Vous n'avez pas encore d'adresse enregistrée</p>
            <button wire:click="openForm" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Ajouter une adresse
            </button>
        </div>
    @endif
</div>
