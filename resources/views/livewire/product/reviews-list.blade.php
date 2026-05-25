<div class="reviews-section bg-white rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Avis des clients</h2>

    <!-- Rating Summary -->
    <div class="grid md:grid-cols-3 gap-8 mb-8">
        <div class="text-center">
            <div class="text-5xl font-bold text-yellow-400">
                {{ number_format($averageRating, 1) }}
            </div>
            <div class="flex justify-center mt-2">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                    </svg>
                @endfor
            </div>
            <div class="text-gray-600 mt-2">{{ $totalReviews }} avis</div>
        </div>

        <!-- Rating Distribution -->
        <div class="md:col-span-2">
            @foreach ($ratingDistribution as $stars => $count)
                <div class="flex items-center mb-2">
                    <button
                        wire:click="filterByRating({{ $stars }})"
                        class="text-sm w-8 {{ $rating === $stars ? 'text-blue-600 font-bold' : 'text-gray-600' }}"
                    >
                        {{ $stars }}★
                    </button>
                    <div class="flex-1 h-2 bg-gray-200 rounded mx-4">
                        <div
                            class="h-full bg-yellow-400 rounded"
                            style="width: {{ $totalReviews > 0 ? ($count / $totalReviews * 100) : 0 }}%"
                        ></div>
                    </div>
                    <span class="text-sm text-gray-600 w-8 text-right">{{ $count }}</span>
                </div>
            @endforeach
            @if ($rating > 0)
                <button
                    wire:click="filterByRating(0)"
                    class="mt-4 text-blue-600 text-sm hover:underline"
                >
                    ✕ Effacer le filtre
                </button>
            @endif
        </div>
    </div>

    <hr class="my-8">

    <!-- Sort Options -->
    <div class="mb-6 flex gap-4">
        <button
            wire:click="sortBy('newest')"
            class="px-4 py-2 rounded {{ $sortBy === 'newest' ? 'bg-blue-600 text-white' : 'bg-gray-100' }}"
        >
            Les plus récents
        </button>
        <button
            wire:click="sortBy('helpful')"
            class="px-4 py-2 rounded {{ $sortBy === 'helpful' ? 'bg-blue-600 text-white' : 'bg-gray-100' }}"
        >
            Les plus utiles
        </button>
    </div>

    <!-- Reviews List -->
    <div class="space-y-6">
        @forelse ($reviews as $review)
            <div class="border-t pt-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Author & Date -->
                        <div class="flex items-center gap-3 mb-2">
                            <div class="font-semibold">{{ $review->author->name }}</div>
                            <span class="text-gray-500 text-sm">
                                {{ $review->created_at->diffForHumans() }}
                            </span>
                            @if ($review->verified_purchase)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                    Achat vérifié
                                </span>
                            @endif
                        </div>

                        <!-- Rating -->
                        <div class="flex mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            @endfor
                        </div>

                        <!-- Title & Comment -->
                        <h3 class="font-semibold text-lg mb-2">{{ $review->title }}</h3>
                        <p class="text-gray-700 mb-4">{{ $review->comment }}</p>

                        <!-- Photos -->
                        @if ($review->hasPhotos())
                            <div class="flex gap-2 mb-4">
                                @foreach ($review->getMedia('photos') as $photo)
                                    <img
                                        src="{{ $photo->getUrl() }}"
                                        alt="Review photo"
                                        class="w-20 h-20 rounded object-cover cursor-pointer hover:opacity-80"
                                        loading="lazy"
                                    >
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Helpful -->
                <div class="flex items-center gap-4 mt-4 text-gray-600">
                    <button
                        wire:click="markHelpful({{ $review->id }})"
                        class="flex items-center gap-2 hover:text-blue-600"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2V9a2 2 0 012-2h.643a2 2 0 011.97 1.516l2.4 12A2 2 0 0115.75 22H21a2 2 0 002-2v-2.5a2 2 0 00-1.972-2H14"></path>
                        </svg>
                        <span class="text-sm">Utile ({{ $review->helpful_count }})</span>
                    </button>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-8">Aucun avis pour le moment.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $reviews->links() }}
    </div>
</div>
