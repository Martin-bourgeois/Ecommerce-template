@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-md">
        <div class="rounded-lg bg-white px-6 py-8 shadow">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Contactez-nous</h1>
            <p class="text-sm text-gray-600 mb-6">
                Avez des questions ? Besoin d'aide ? Remplissez le formulaire ci-dessous et nous vous répondrons dans les 24 heures.
            </p>

            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Votre nom *
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                        placeholder="Jean Dupont"
                        value="{{ old('name', auth()->user()?->name ?? '') }}"
                    >
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Adresse email *
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                        placeholder="jean@exemple.com"
                        value="{{ old('email', auth()->user()?->email ?? '') }}"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">
                        Sujet *
                    </label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('subject') border-red-500 @enderror"
                        placeholder="Questions sur une commande"
                        value="{{ old('subject') }}"
                    >
                    @error('subject')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">
                        Message *
                    </label>
                    <textarea
                        name="message"
                        id="message"
                        required
                        rows="5"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm @error('message') border-red-500 @enderror"
                        placeholder="Décrivez votre question ou problème..."
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="newsletter"
                        id="newsletter"
                        value="1"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        {{ old('newsletter') ? 'checked' : '' }}
                    >
                    <label for="newsletter" class="ml-2 block text-sm text-gray-700">
                        M'abonner à la newsletter pour recevoir les promotions
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Envoyer le message
                </button>
            </form>

            <div class="mt-6 space-y-4 text-center text-sm text-gray-600">
                <div>
                    <p class="font-medium text-gray-900">Autres moyens de nous contacter:</p>
                </div>
                <div>
                    <p>📧 <a href="mailto:glorygandigbe2@gmail.com" class="text-blue-600 hover:text-blue-500">glorygandigbe2@gmail.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
