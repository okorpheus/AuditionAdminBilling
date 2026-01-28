<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('clients', 'clients.index')
    ->middleware(['auth', 'verified'])
    ->name('clients.index');

require __DIR__.'/settings.php';
