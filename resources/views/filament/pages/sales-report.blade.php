<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Filtres</h2>
            {{ $this->form }}
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Commandes</p>
                <p class="mt-2 text-3xl font-bold">{{ $this->data['total_orders'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Revenu total</p>
                <p class="mt-2 text-3xl font-bold">{{ number_format($this->data['total_revenue'] ?? 0, 2) }}€</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Panier moyen</p>
                <p class="mt-2 text-3xl font-bold">{{ number_format($this->data['avg_order_value'] ?? 0, 2) }}€</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500">Nouveaux clients</p>
                <p class="mt-2 text-3xl font-bold">{{ $this->data['new_customers'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
