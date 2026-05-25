@extends('layouts.app')

@section('title', 'Catalogue - ' . config('app.name'))
@section('breadcrumbs')
    <span>/</span>
    <span class="text-gray-900">Catalogue</span>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Sidebar Filters -->
    <aside class="lg:col-span-1">
        <div class="bg-white rounded-lg p-6 sticky top-20">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Filtres</h3>

            <form action="{{ route('catalog.index') }}" method="GET" class="space-y-6">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Recherche</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Mot-clé..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Categories -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Catégorie</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Toutes</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Range -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Gamme de prix</label>
                    <div class="space-y-2">
                        <input 
                            type="number" 
                            name="min_price" 
                            value="{{ request('min_price') }}"
                            placeholder="Min €"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <input 
                            type="number" 
                            name="max_price" 
                            value="{{ request('max_price') }}"
                            placeholder="Max €"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                </div>

                <!-- Sort -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Trier par</label>
                    <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="newest" @selected(request('sort') === 'newest' || !request('sort'))>Plus récent</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Prix: bas à haut</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Prix: haut à bas</option>
                        <option value="popular" @selected(request('sort') === 'popular')>Plus vendu</option>
                        <option value="rating" @selected(request('sort') === 'rating')>Mieux noté</option>
                    </select>
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-semibold">
                    Appliquer les filtres
                </button>
                <a href="{{ route('catalog.index') }}" class="block w-full text-center py-2 text-gray-600 hover:text-gray-900">
                    Réinitialiser
                </a>
            </form>
        </div>
    </aside>

    <!-- Products Grid -->
    <div class="lg:col-span-3">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">
                @if (request('search'))
                    Résultats pour "{{ request('search') }}"
                @elseif (request('category'))
                    {{ collect($categories)->firstWhere('slug', request('category'))?->name ?? 'Catalogue' }}
                @else
                    Tous les produits
                @endif
            </h1>
            <p class="text-gray-600">{{ $products->total() }} produit(s)</p>
        </div>

        <!-- Products Grid -->
        @if ($products->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun produit trouvé</h3>
                <p class="text-gray-600 mb-4">Essayez d'autres filtres ou mots-clés</p>
                <a href="{{ route('catalog.index') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Voir tous les produits
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
