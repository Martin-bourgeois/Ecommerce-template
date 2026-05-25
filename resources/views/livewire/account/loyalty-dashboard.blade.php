<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Points -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white">
            <p class="text-sm font-semibold opacity-90">Points de fidélité</p>
            <p class="text-4xl font-bold mt-2">{{ number_format($points) }}</p>
        </div>

        <!-- Tier -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white">
            <p class="text-sm font-semibold opacity-90">Niveau</p>
            <p class="text-4xl font-bold mt-2 capitalize">{{ $tier }}</p>
        </div>

        <!-- Total Spent -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white">
            <p class="text-sm font-semibold opacity-90">Total dépensé</p>
            <p class="text-3xl font-bold mt-2"><x-price :price="$totalSpent" /></p>
        </div>
    </div>

    <!-- How Points Work -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Comment ça marche</h3>
        <ul class="space-y-3 text-gray-700">
            <li class="flex items-start space-x-3">
                <span class="text-blue-600 font-bold">1</span>
                <span>Gagnez 1 point pour chaque euro dépensé</span>
            </li>
            <li class="flex items-start space-x-3">
                <span class="text-blue-600 font-bold">2</span>
                <span>Accumulez les points pour des récompenses</span>
            </li>
            <li class="flex items-start space-x-3">
                <span class="text-blue-600 font-bold">3</span>
                <span>Déblocquez des offres exclusives selon votre niveau</span>
            </li>
        </ul>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Transactions récentes</h3>

        @if (count($recentTransactions) > 0)
            <div class="space-y-3">
                @foreach ($recentTransactions as $transaction)
                    <div class="flex justify-between items-center py-3 border-b last:border-b-0">
                        <div>
                            <p class="text-gray-900 font-semibold">{{ $transaction['description'] }}</p>
                            <p class="text-sm text-gray-600">{{ $transaction['created_at'] }}</p>
                        </div>
                        <p class="font-bold text-blue-600">+{{ $transaction['points'] }} pts</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 text-center py-8">Aucune transaction</p>
        @endif
    </div>
</div>
