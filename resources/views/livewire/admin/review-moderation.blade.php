<div class="review-moderation bg-white rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Modération des avis</h2>
        <span class="bg-red-100 text-red-800 px-4 py-2 rounded-lg font-semibold">
            {{ $totalPending }} en attente
        </span>
    </div>

    @if ($reviews->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold">Produit</th>
                        <th class="text-left px-4 py-3 font-semibold">Auteur</th>
                        <th class="text-left px-4 py-3 font-semibold">Note</th>
                        <th class="text-left px-4 py-3 font-semibold">Commentaire</th>
                        <th class="text-left px-4 py-3 font-semibold">Date</th>
                        <th class="text-center px-4 py-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reviews as $review)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('products.show', $review->product) }}" class="text-blue-600 hover:underline">
                                    {{ $review->product->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                {{ $review->author->name }}
                                @if ($review->verified_purchase)
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded ml-2">
                                        Vérifié
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="max-w-xs">
                                    <p class="font-semibold text-sm">{{ $review->title }}</p>
                                    <p class="text-sm text-gray-600 truncate">{{ $review->comment }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $review->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex gap-2 justify-center">
                                    <button
                                        wire:click="approve({{ $review->id }})"
                                        class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700"
                                    >
                                        Approuver
                                    </button>
                                    <button
                                        wire:click="reject({{ $review->id }})"
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700"
                                    >
                                        Rejeter
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Aucun avis en attente de modération. ✓</p>
        </div>
    @endif
</div>
