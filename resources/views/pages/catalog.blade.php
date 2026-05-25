@extends('layouts.app')

@section('content')
<div class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Catalogue</h1>
                <nav class="text-sm text-gray-500">
                    <a href="/" class="hover:text-gray-700">Accueil</a>
                    <span class="mx-2">/</span>
                    <span>Catalogue</span>
                </nav>
            </div>

            <!-- Search Bar -->
            <div class="mt-6">
                @livewire('catalog.product-search')
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar Filters -->
            <aside class="lg:col-span-1">
                @livewire('catalog.product-filters')
            </aside>

            <!-- Product Grid -->
            <main class="lg:col-span-3">
                @livewire('catalog.product-grid')
            </main>
        </div>
    </div>
</div>
@endsection
