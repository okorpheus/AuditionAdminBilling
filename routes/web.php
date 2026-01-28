<?php

use App\Models\Invoice;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('clients', 'clients.index')->name('clients');
    Route::view('contacts', 'contacts.index')->name('contacts');
    Route::view('products', 'products.index')->name('products');
    Route::view('invoices', 'invoices.index')->name('invoices');
    Route::get('invoices/{invoice}/edit',
        fn (Invoice $invoice) => view('invoices.edit', compact('invoice')))->name('invoices.edit');
});

// Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');

// Route::view('clients', 'clients.index')
//    ->middleware(['auth', 'verified'])
//    ->name('clients.index');

require __DIR__.'/settings.php';
