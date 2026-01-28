<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'contact_id',
        'payment_date',
        'status',
        'payment_method',
        'reference',
        'stripe_payment_intent_id',
        'fee_amount',
        'amount',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => MoneyCast::class,
        'fee_amount' => MoneyCast::class,
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function client(): BelongsTo
    {
        return $this->invoice->client();
    }
}
