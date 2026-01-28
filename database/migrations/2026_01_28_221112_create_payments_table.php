<?php

use App\Models\Contact;
use App\Models\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Invoice::class)->constrained();
            $table->foreignIdFor(Contact::class)->nullable()->constrained();
            $table->date('payment_date');
            $table->string('status')->default('pending'); // Accordint to claude,needed by Stripe. pending, completed, failed, refunded
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->integer('fee_amount')->nullable();
            $table->integer('amount');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
