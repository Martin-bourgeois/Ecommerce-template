@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                Se connecter
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Pas encore de compte?
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    S'inscrire gratuitement
                </a>
            </p>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="{{ route('login.store') }}">
            @csrf

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Adresse email
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                    placeholder="votre.email@exemple.com"
                    value="{{ old('email') }}"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
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
                    placeholder="Votre mot de passe"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        value="1"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Se souvenir de moi
                    </label>
                </div>
                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                        Mot de passe oublié?
                    </a>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
            >
                Se connecter
            </button>
        </form>

        <!-- Divider -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-gray-50 text-gray-500">Ou</span>
            </div>
        </div>

        <!-- Guest Checkout -->
        <p class="text-center text-sm text-gray-600">
            Vous préférez continuer en tant qu'invité?
            <a href="{{ route('catalog.index') }}" class="font-medium text-blue-600 hover:text-blue-500">
                Continuer vos achats
            </a>
        </p>
    </div>
</div>
@endsection
