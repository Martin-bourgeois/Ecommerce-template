@extends('account.layout')

@section('account-content')
<div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Mon Profil</h1>

    @livewire('account.profile-form')
</div>
@endsection
