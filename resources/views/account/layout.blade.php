@extends('layouts.app')

@section('title', 'Mon compte - ' . config('app.name'))
@section('breadcrumbs')
    <span>/</span>
    <a href="{{ route('account.profile') }}" class="hover:text-gray-900">Mon compte</a>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    <!-- Sidebar Navigation -->
    <aside class="md:col-span-1">
        <nav class="bg-white rounded-lg shadow-sm p-6 sticky top-20">
            <h3 class="font-bold text-gray-900 mb-4">Mon compte</h3>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('account.profile') }}" class="block px-4 py-2 rounded hover:bg-blue-50 @active('account.profile')">Profil</a>
                </li>
                <li>
                    <a href="{{ route('account.orders') }}" class="block px-4 py-2 rounded hover:bg-blue-50">Mes commandes</a>
                </li>
                <li>
                    <a href="{{ route('account.addresses') }}" class="block px-4 py-2 rounded hover:bg-blue-50">Adresses</a>
                </li>
                <li>
                    <a href="{{ route('account.loyalty') }}" class="block px-4 py-2 rounded hover:bg-blue-50">Fidélité</a>
                </li>
                <li>
                    <a href="{{ route('account.support.tickets') }}" class="block px-4 py-2 rounded hover:bg-blue-50">Support</a>
                </li>
                <li class="border-t pt-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded">Déconnexion</button>
                    </form>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Content -->
    <main class="md:col-span-3">
        @yield('account-content')
    </main>
</div>
@endsection
