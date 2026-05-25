<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Ce fichier est requis par routes/web.php
// Routes d'authentification pour le frontend

// Logout route for web
Route::post('/logout', function () {
    Auth::logout();
    Session::invalidate();
    Session::regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

// Login route - customer login page
Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

// Register routes - custom registration page
Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.store');


