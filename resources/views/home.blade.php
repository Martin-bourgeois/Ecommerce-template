@extends('layouts.app')

@section('title', 'Accueil - Plateforme E-commerce de qualité')
@section('description', 'Découvrez notre sélection exclusive de produits premium avec les meilleurs prix. Nouveautés, meilleures ventes, et promotions exclusives.')
@section('keywords', 'e-commerce, achats en ligne, produits qualité, promotions')

@push('meta')
    <meta property="og:title" content="Accueil - Plateforme E-commerce">
    <meta property="og:description" content="Découvrez notre sélection exclusive de produits premium">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('home') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#2563eb">
@endpush

@section('content')
<!-- Hero Section with CTAs -->
<section class="bg-gradient-to-r from-blue-600 via-blue-700 to-blue-900 text-white rounded-xl overflow-hidden mb-16 shadow-lg">
    <div class="px-6 py-16 md:px-12 md:py-32 flex flex-col md:flex-row items-center justify-between">
        <div class="md:w-1/2 mb-10 md:mb-0">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                Bienvenue sur votre <span class="text-blue-200">plateforme shopping</span>
            </h1>
            <p class="text-lg md:text-xl mb-8 opacity-95 leading-relaxed">
                Découvrez une collection curatée de produits premium avec les meilleurs prix du marché. Livraison rapide, qualité garantie.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('catalog.index') }}" 
                   class="bg-white text-blue-600 px-8 py-4 rounded-lg font-bold hover:bg-blue-50 transition-colors shadow-md inline-flex items-center justify-center">
                    <span>🛍️ Commencer le shopping</span>
                </a>
                @guest
                    <a href="{{ route('catalog.index') }}" 
                       class="bg-blue-500 border-2 border-white text-white px-8 py-4 rounded-lg font-bold hover:bg-blue-600 transition-colors inline-flex items-center justify-center">
                        <span>✨ Parcourir le catalogue</span>
                    </a>
                @endguest
                @auth
                    <a href="{{ route('catalog.index') }}" 
                       class="bg-blue-500 border-2 border-white text-white px-8 py-4 rounded-lg font-bold hover:bg-blue-600 transition-colors inline-flex items-center justify-center">
                        <span>� Voir le catalogue</span>
                    </a>
                @endauth
            </div>
        </div>
        <div class="md:w-1/2 text-center px-4">
            <div class="bg-white/10 rounded-2xl p-12 backdrop-blur-sm border border-white/20">
                <svg class="w-56 h-56 mx-auto text-white opacity-90 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- Active Promotions Banner -->
@if ($activePromotions->count())
    <section class="mb-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($activePromotions as $promotion)
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-6 border-2 border-amber-200 shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <span class="inline-block bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold mb-2">
                                🎉 PROMOTION
                            </span>
                            <h3 class="text-xl font-bold text-gray-900">{{ $promotion->name }}</h3>
                        </div>
                    </div>
                    <p class="text-gray-700 mb-4">{{ Str::limit($promotion->description, 100) }}</p>
                    <div class="flex items-center justify-between pt-4 border-t border-amber-200">
                        <span class="text-sm text-gray-600">Jusqu'au {{ $promotion->ends_at?->format('d/m/Y') ?? 'indéterminé' }}</span>
                        <a href="{{ route('catalog.index') }}" class="text-amber-600 hover:text-amber-700 font-bold text-sm">
                            Découvrir →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<!-- Avantages Section -->
<section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
    <div class="bg-white rounded-lg p-6 shadow-md text-center hover:shadow-lg transition-shadow">
        <div class="text-4xl mb-3">🚚</div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Livraison Rapide</h3>
        <p class="text-gray-600 text-sm">Livraison en 2-3 jours ouvrables</p>
    </div>
    <div class="bg-white rounded-lg p-6 shadow-md text-center hover:shadow-lg transition-shadow">
        <div class="text-4xl mb-3">🛡️</div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Garantie 100%</h3>
        <p class="text-gray-600 text-sm">Satisfait ou remboursé en 30 jours</p>
    </div>
    <div class="bg-white rounded-lg p-6 shadow-md text-center hover:shadow-lg transition-shadow">
        <div class="text-4xl mb-3">💳</div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Paiement Sécurisé</h3>
        <p class="text-gray-600 text-sm">Paiement SSL crypté et sécurisé</p>
    </div>
    <div class="bg-white rounded-lg p-6 shadow-md text-center hover:shadow-lg transition-shadow">
        <div class="text-4xl mb-3">⭐</div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Service Client</h3>
        <p class="text-gray-600 text-sm">Disponible 7j/7 pour vous aider</p>
    </div>
</section>

<!-- Featured Products Section -->
@if ($featuredProducts->count())
    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">✨ Sélection vedette</h2>
                <p class="text-gray-600 mt-2">Nos produits les plus populaires, spécialement choisis pour vous</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="hidden md:flex text-blue-600 hover:text-blue-700 font-semibold items-center space-x-2 text-lg">
                <span>Voir tous</span>
                <span>→</span>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($featuredProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        <div class="text-center mt-8 md:hidden">
            <a href="{{ route('catalog.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold text-lg">
                Voir tous nos produits →
            </a>
        </div>
    </section>
@endif

<!-- Categories Section -->
@if ($categories->count())
    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">🏷️ Parcourir par catégorie</h2>
                <p class="text-gray-600 mt-2">Trouvez exactement ce que vous cherchez</p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($categories as $category)
                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" 
                   class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 text-center hover:from-blue-100 hover:to-blue-200 transition-colors shadow-md hover:shadow-lg">
                    <div class="text-3xl mb-3">📦</div>
                    <h3 class="font-bold text-gray-900 mb-2">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $category->products_count }} produit{{ $category->products_count > 1 ? 's' : '' }}</p>
                </a>
            @endforeach
        </div>
    </section>
@endif

<!-- New Arrivals Section -->
@if ($newArrivals->count())
    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">🆕 Nouveautés</h2>
                <p class="text-gray-600 mt-2">Découvrez nos dernières arrivées en magasin</p>
            </div>
            <a href="{{ route('catalog.index', ['sort' => 'newest']) }}" class="hidden md:flex text-blue-600 hover:text-blue-700 font-semibold items-center space-x-2 text-lg">
                <span>Voir tous</span>
                <span>→</span>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($newArrivals as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        <div class="text-center mt-8 md:hidden">
            <a href="{{ route('catalog.index', ['sort' => 'newest']) }}" class="text-blue-600 hover:text-blue-700 font-semibold text-lg">
                Voir toutes les nouveautés →
            </a>
        </div>
    </section>
@endif

<!-- Bestsellers Section -->
@if ($bestsellers->count())
    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">🔥 Meilleures ventes</h2>
                <p class="text-gray-600 mt-2">Les produits les plus appréciés par nos clients</p>
            </div>
            <a href="{{ route('catalog.index', ['sort' => 'popular']) }}" class="hidden md:flex text-blue-600 hover:text-blue-700 font-semibold items-center space-x-2 text-lg">
                <span>Voir tous</span>
                <span>→</span>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($bestsellers as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        <div class="text-center mt-8 md:hidden">
            <a href="{{ route('catalog.index', ['sort' => 'popular']) }}" class="text-blue-600 hover:text-blue-700 font-semibold text-lg">
                Voir les meilleures ventes →
            </a>
        </div>
    </section>
@endif

<!-- Newsletter Section -->
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-xl p-8 md:p-12 mb-16">
    <div class="max-w-2xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">📧 Restez informé</h2>
        <p class="text-lg opacity-95 mb-8">Inscrivez-vous à notre newsletter pour recevoir nos meilleures offres et nouveautés en priorité.</p>
        <a href="{{ route('contact.form') }}" 
           class="inline-flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-lg font-bold transition-colors">
            💌 Contacter et s'abonner
        </a>
        <p class="text-sm opacity-75 mt-4">Nous respectons votre vie privée. Désinscription possible à tout moment.</p>
    </div>
</section>

<!-- CTA Final Section -->
<section class="text-center py-12">
    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Prêt à faire vos achats ?</h2>
    <p class="text-xl text-gray-600 mb-8">Parcourez notre catalogue complet et trouvez exactement ce que vous cherchez.</p>
    <a href="{{ route('catalog.index') }}" 
       class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-12 py-4 rounded-lg font-bold text-lg transition-colors shadow-lg">
        🛒 Accéder au catalogue
    </a>
</section>

<!-- Schema.org Structured Data for SEO -->
@push('scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ config('app.name') }}",
  "url": "{{ config('app.url') }}",
  "description": "Plateforme d'e-commerce premium avec sélection de produits de qualité",
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "{{ route('catalog.index') }}?search={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "{{ config('app.name') }}",
  "url": "{{ config('app.url') }}",
  "logo": "{{ asset('logo.png') }}",
  "sameAs": [
    "https://www.facebook.com/yourpage",
    "https://www.twitter.com/yourpage",
    "https://www.instagram.com/yourpage"
  ]
}
</script>
@endpush
@endsection
