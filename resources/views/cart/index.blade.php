@extends('layouts.app')

@section('title', 'Mon panier - ' . config('app.name'))
@section('breadcrumbs')
    <span>/</span>
    <span class="text-gray-900">Mon panier</span>
@endsection

@section('content')
<!-- Cart Component (Livewire) -->
@livewire('cart.cart-page')
@endsection
