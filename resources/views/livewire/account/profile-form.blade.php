<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Mon profil</h2>

    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Nom complet</label>
            <input 
                type="text" 
                wire:model="name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
            <input 
                type="email" 
                wire:model="email"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Phone -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Téléphone</label>
            <input 
                type="tel" 
                wire:model="phone"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @error('phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Submit -->
        <button 
            type="submit"
            wire:loading.attr="disabled"
            class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-semibold"
        >
            @if ($saving)
                <span wire:loading>Sauvegarde...</span>
            @else
                Mettre à jour
            @endif
        </button>

        @if ($message)
            <div class="p-3 rounded-lg @if(strpos($message, 'succès') !== false) bg-green-50 text-green-700 @else bg-red-50 text-red-700 @endif">
                {{ $message }}
            </div>
        @endif
    </form>
</div>
