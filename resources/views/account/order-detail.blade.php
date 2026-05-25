@extends('account.layout')

@section('account-content')
<div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
    <div class="mb-6">
        <a href="{{ route('account.orders') }}" class="text-blue-600 hover:text-blue-700">← Retour aux commandes</a>
    </div>

    @livewire('account.order-detail')
</div>
@endsection
