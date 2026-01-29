<?php

use App\Http\Controllers\CustomerInvoiceController;
use App\Http\Controllers\StripeController;
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
        fn(Invoice $invoice) => view('invoices.edit', compact('invoice')))->name('invoices.edit');
    Route::get('invoices/{invoice}', CustomerInvoiceController::class)->name('invoices.show');
    Route::view('payments', 'payments.index')->name('payments');
});

// Testing Stripe
Route::get('stripe', [StripeController::class, 'index'])->name('stripe.index');
Route::post('/checkout ', [StripeController::class, 'checkout'])->name('stripe.checkout');
Route::get('/success', [StripeController::class, 'success'])->name('stripe.success');

require __DIR__.'/settings.php';
