<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Gestion des Promotions</h2>
        <button wire:click="create"
                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
            + Nouvelle Promotion
        </button>
    </div>

    <!-- Search & Filter -->
    <div class="mb-6 flex gap-4">
        <input type="text"
               wire:model.live="search"
               placeholder="Rechercher..."
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <select wire:model.live="filterStatus"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <option value="">Tous les statuts</option>
            <option value="active">Actif</option>
            <option value="inactive">Inactif</option>
        </select>
    </div>

    <!-- Create/Edit Form -->
    @if ($showCreateForm || $editingId)
        <div class="mb-6 p-4 border-2 border-blue-300 rounded-lg bg-blue-50">
            <h3 class="font-semibold text-lg mb-4">
                {{ $editingId ? 'Éditer Promotion' : 'Nouvelle Promotion' }}
            </h3>

            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" wire:model="name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               required>
                        @error('name')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" wire:model="slug"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               required>
                        @error('slug')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select wire:model="type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">Pourcentage (%)</option>
                            <option value="fixed_amount">Montant fixe (€)</option>
                            <option value="free_shipping">Livraison gratuite</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cible</label>
                        <select wire:model="target"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="order">Panier entier</option>
                            <option value="product">Produit</option>
                            <option value="category">Catégorie</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valeur</label>
                        <input type="number" wire:model="value" step="0.01" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                </div>

                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive">
                        <span class="text-sm">Actif</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isStackable">
                        <span class="text-sm">Cumulable</span>
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition">
                        Enregistrer
                    </button>
                    <button type="button" wire:click="resetForm"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 rounded-lg transition">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Promotions Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Nom</th>
                    <th class="px-4 py-3 text-left font-semibold">Type</th>
                    <th class="px-4 py-3 text-left font-semibold">Valeur</th>
                    <th class="px-4 py-3 text-left font-semibold">Cumulable</th>
                    <th class="px-4 py-3 text-left font-semibold">Statut</th>
                    <th class="px-4 py-3 text-left font-semibold">Utilisations</th>
                    <th class="px-4 py-3 text-center font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($promotions as $promo)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $promo->name }}</td>
                        <td class="px-4 py-3">{{ $promo->type->label() }}</td>
                        <td class="px-4 py-3">{{ $promo->type->format((int)($promo->value * 100)) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded
                                {{ $promo->is_stackable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $promo->is_stackable ? 'Oui' : 'Non' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded
                                {{ $promo->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $promo->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $promo->usage_count }} / {{ $promo->usage_limit ?? '∞' }}</td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <button wire:click="edit({{ $promo->id }})"
                                    class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded">
                                ✎
                            </button>
                            <button wire:click="$dispatch('deletePromotion', { id: {{ $promo->id }} })"
                                    class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded"
                                    onclick="return confirm('Êtes-vous sûr ?')">
                                ✕
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-600">
                            Aucune promotion trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($promotions->hasPages())
        <div class="mt-6">
            {{ $promotions->links() }}
        </div>
    @endif
</div>
