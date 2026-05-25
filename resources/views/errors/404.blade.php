@extends('layouts.app')

@section('content')
<div class="text-center py-12">
    <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
    <h2 class="text-3xl font-semibold text-gray-700 mb-2">Page non trouvée</h2>
    <p class="text-gray-600 mb-8">Désolé, la page que vous recherchez n'existe pas.</p>

    <a href="{{ route('home') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700">
        Retour à l'accueil
    </a>
</div>
@endsection
