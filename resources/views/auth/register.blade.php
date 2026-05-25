@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                Créer un compte
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ou
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    se connecter à votre compte
                </a>
            </p>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="{{ route('register.store') }}">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nom complet
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                    placeholder="Votre nom"
                    value="{{ old('name') }}"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Adresse email
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                    placeholder="votre.email@exemple.com"
                    value="{{ old('email') }}"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Mot de passe
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('password') border-red-500 @enderror"
                    placeholder="Au moins 8 caractères"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirmer le mot de passe
                </label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
                    placeholder="Confirmez votre mot de passe"
                >
            </div>

            <div class="flex items-center">
                <input
                    id="newsletter"
                    name="newsletter"
                    type="checkbox"
                    value="1"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <label for="newsletter" class="ml-2 block text-sm text-gray-900">
                    S'abonner à notre newsletter pour les actualités et promotions
                </label>
            </div>

            <button
                type="submit"
                class="group relative w-full flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                S'inscrire
            </button>
        </form>

        @if (session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
