<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Filtres</h2>
            {{ $this->form }}
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Produits les plus vendus</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left py-2 px-4">Nom</th>
                            <th class="text-left py-2 px-4">SKU</th>
                            <th class="text-left py-2 px-4">Quantité</th>
                            <th class="text-left py-2 px-4">Prix</th>
                            <th class="text-left py-2 px-4">Revenu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $product)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $product['name'] }}</td>
                                <td class="py-3 px-4 text-gray-500">{{ $product['sku'] }}</td>
                                <td class="py-3 px-4">{{ $product['quantity'] }}</td>
                                <td class="py-3 px-4">{{ number_format($product['price'], 2) }}€</td>
                                <td class="py-3 px-4 font-medium">{{ number_format($product['revenue'], 2) }}€</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-500">Aucun produit vendu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Produits en stock bas</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left py-2 px-4">Nom</th>
                            <th class="text-left py-2 px-4">SKU</th>
                            <th class="text-left py-2 px-4">Stock</th>
                            <th class="text-left py-2 px-4">Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                            <tr class="border-b hover:bg-gray-50"
                                @class([
                                    'bg-red-50' => $product['stock'] == 0,
                                    'bg-yellow-50' => $product['stock'] > 0 && $product['stock'] <= 3,
                                ])>
                                <td class="py-3 px-4">{{ $product['name'] }}</td>
                                <td class="py-3 px-4 text-gray-500">{{ $product['sku'] }}</td>
                                <td class="py-3 px-4">
                                    <span @class([
                                        'font-medium text-red-600' => $product['stock'] == 0,
                                        'font-medium text-yellow-600' => $product['stock'] > 0 && $product['stock'] <= 3,
                                    ])>
                                        {{ $product['stock'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ number_format($product['price'], 2) }}€</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">Tous les produits ont du stock</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
