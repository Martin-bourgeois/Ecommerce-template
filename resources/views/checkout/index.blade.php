@extends('layouts.app')

@section('title', 'Finaliser ma commande - ' . config('app.name'))
@section('breadcrumbs')
    <span>/</span>
    <a href="{{ route('cart.index') }}" class="hover:text-gray-900">Mon panier</a>
    <span>/</span>
    <span class="text-gray-900">Checkout</span>
@endsection

@section('content')
<!-- Checkout Component (Livewire) -->
@livewire('checkout.checkout-form')
@endsection
