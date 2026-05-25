<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@isset($title) {{ $title }} - @endisset {{ config('app.name') }}</title>
    <meta name="description" content="@isset($description) {{ $description }} @else E-commerce en ligne @endisset">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-white">
    <!-- Header -->
    <header class="bg-white border-b sticky top-0 z-40">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Top bar: Logo, Search, Cart, Account -->
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600 hover:text-blue-700 hover:opacity-80 transition cursor-pointer">
                        {{ config('app.name') }}
                    </a>
                </div>

                <!-- Search bar (hidden on mobile) -->
                <form action="{{ route('catalog.index') }}" method="GET" class="hidden md:flex flex-1 mx-8">
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Rechercher des produits..."
                            value="{{ request('search') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Right side: Mini cart, Account -->
                <div class="flex items-center space-x-4">
                    <!-- Mini Cart (Livewire) -->
                    @if (auth()->check())
                        @livewire('cart.mini-cart')
                    @else
                        <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span class="absolute top-1 right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">0</span>
                        </a>
                    @endif

                    <!-- Account / Auth -->
                    @auth
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="p-2 text-gray-600 hover:text-gray-900">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="{{ route('account.profile') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Mon compte</a>
                                <a href="{{ route('account.orders') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Mes commandes</a>
                                <a href="{{ route('account.loyalty') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Fidélité</a>
                                <a href="{{ route('account.support.tickets') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Support</a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-800 hover:bg-gray-100">Déconnexion</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="/login" class="text-gray-600 hover:text-gray-900">Connexion</a>
                        <a href="/register" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">S'inscrire</a>
                    @endauth
                </div>
            </div>

            <!-- Category Navigation -->
            <div class="hidden md:flex border-t">
                <div x-data="{ open: false }" class="relative group">
                    <button @click="open = !open" class="px-4 py-3 text-gray-700 hover:text-blue-600 flex items-center space-x-1">
                        <span>Catégories</span>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" class="absolute left-0 mt-0 w-64 bg-white rounded-lg shadow-lg p-4 z-50">
                        @forelse ($allCategories ?? [] as $category)
                            <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="block px-4 py-2 text-gray-800 hover:bg-blue-50 rounded">
                                {{ $category->name }}
                            </a>
                        @empty
                            <p class="text-gray-500 px-4 py-2">Aucune catégorie</p>
                        @endforelse
                    </div>
                </div>

                <a href="{{ route('catalog.index') }}" class="px-4 py-3 text-gray-700 hover:text-blue-600">Tous les produits</a>
            </div>
        </nav>
    </header>

    <!-- Flash Messages -->
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 max-w-7xl mx-auto mt-4">
            <div class="flex items-start">
                <div class="flex-1">
                    <p class="font-medium text-red-800">Des erreurs se sont produites:</p>
                    <ul class="mt-2 list-disc list-inside space-y-1 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 max-w-7xl mx-auto mt-4">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Breadcrumbs -->
    @unless (Route::currentRouteName() === 'home')
        <nav class="bg-gray-50 py-3 px-4 mb-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <a href="{{ route('home') }}" class="hover:text-gray-900">Accueil</a>
                    @isset($breadcrumbs) {{ $breadcrumbs }} @endisset
                </div>
            </div>
        </nav>
    @endunless

    <!-- Main Content (for Livewire) -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div>
                    <h3 class="text-white font-bold mb-4">{{ config('app.name') }}</h3>
                    <p class="text-sm text-gray-400">Votre destination d'e-commerce de confiance avec une large sélection de produits.</p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="text-white font-bold mb-4">Navigation</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Accueil</a></li>
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-white">Catalogue</a></li>
                        <li><a href="{{ route('account.loyalty') }}" class="hover:text-white">Fidélité</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="text-white font-bold mb-4">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white">Aide & FAQ</a></li>
                        <li><a href="#" class="hover:text-white">Contactez-nous</a></li>
                        <li><a href="#" class="hover:text-white">Politique de retour</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h4 class="text-white font-bold mb-4">Newsletter</h4>
                    <form class="flex">
                        <input type="email" placeholder="Votre email" class="flex-1 px-3 py-2 rounded-l-lg bg-gray-800 text-white placeholder-gray-500 focus:outline-none">
                        <button type="submit" class="bg-blue-600 px-4 py-2 rounded-r-lg hover:bg-blue-700">S'inscrire</button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
                    <div class="flex space-x-4 mt-4 md:mt-0">
                        <a href="#" class="hover:text-white">Confidentialité</a>
                        <a href="#" class="hover:text-white">Conditions</a>
                        <a href="#" class="hover:text-white">Réseaux sociaux</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    @livewireScripts
</body>
</html>
