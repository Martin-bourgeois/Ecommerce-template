<div class="review-form bg-white rounded-lg p-6">
    <h3 class="text-xl font-bold mb-6">Laisser un avis</h3>

    @if (!auth()->check())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-blue-800">
                <a href="{{ route('login') }}" class="underline font-semibold">Connectez-vous</a>
                pour laisser un avis.
            </p>
        </div>
    @elseif (!$canReview)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <p class="text-amber-800">
                Vous devez avoir une commande livrée de ce produit pour laisser un avis.
            </p>
        </div>
    @else
        <!-- Success Message -->
        @if ($successMessage)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-green-800">{{ $successMessage }}</p>
            </div>
        @endif

        <!-- Error Message -->
        @if ($errorMessage)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-800">{{ $errorMessage }}</p>
            </div>
        @endif

        <form wire:submit="submit" class="space-y-6">
            <!-- Rating -->
            <div>
                <label class="block font-semibold mb-3">Note</label>
                <div class="flex gap-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <button
                            type="button"
                            wire:click="$set('rating', {{ $i }})"
                            class="focus:outline-none transition"
                        >
                            <svg
                                class="w-8 h-8 {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-200' }} cursor-pointer"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                            </svg>
                        </button>
                    @endfor
                </div>
                @error('rating')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block font-semibold mb-2">Titre</label>
                <input
                    type="text"
                    id="title"
                    wire:model="title"
                    placeholder="Résumez votre avis en quelques mots..."
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    maxlength="255"
                >
                @error('title')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Comment -->
            <div>
                <label for="comment" class="block font-semibold mb-2">Commentaire</label>
                <textarea
                    id="comment"
                    wire:model="comment"
                    placeholder="Partagez votre expérience (minimum 20 caractères)..."
                    rows="5"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    maxlength="5000"
                ></textarea>
                <p class="text-sm text-gray-500 mt-1">
                    {{ strlen($comment) }}/5000 caractères
                </p>
                @error('comment')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Photos Upload -->
            <div>
                <label class="block font-semibold mb-2">Photos (optionnel, max 3)</label>
                <div class="border-2 border-dashed rounded-lg p-4">
                    <input
                        type="file"
                        wire:model="photos"
                        multiple
                        accept="image/*"
                        class="hidden"
                        id="photo-input"
                    >
                    <label for="photo-input" class="cursor-pointer block text-center">
                        <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-600">Cliquez pour ajouter des photos</p>
                    </label>
                </div>

                <!-- Photos Preview -->
                @if ($photos)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                        @foreach ($photos as $index => $photo)
                            <div class="relative">
                                <img
                                    src="{{ $photo->temporaryUrl() }}"
                                    alt="Preview"
                                    class="w-full h-24 object-cover rounded"
                                >
                                <button
                                    type="button"
                                    wire:click="removePhoto({{ $index }})"
                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700"
                                >
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Submit -->
            <div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                >
                    @if ($loading)
                        <span wire:loading>Envoi en cours...</span>
                    @else
                        Publier mon avis
                    @endif
                </button>
            </div>
        </form>
    @endif
</div>
