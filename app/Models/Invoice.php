<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    public static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            $invoice->invoice_number ??= static::generateInvoiceNumber();
            $invoice->uuid           = (string) Str::uuid();
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = date('y').'-';

        do {
            $number = $prefix.str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }

    protected $fillable = [
        'invoice_number',
        'client_id',
        'status',
        'invoice_date',
        'sent_at',
        'due_date',
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'total'          => MoneyCast::class,
        'total_payments' => MoneyCast::class,
        'balance_due'    => MoneyCast::class,
        'status'         => InvoiceStatus::class,
        'invoice_date'   => 'date',
        'due_date'       => 'date',
        'sent_at'        => 'date',
    ];

    /**
     * Get the route key for the model.
     * This tells Laravel to use the 'uuid' column for route model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculateTotal(): void
    {
        $this->attributes['total'] = $this->lines()->sum('amount');
        $this->saveQuietly();
    }

    public function recalculateTotalPayments(): void
    {
        $this->attributes['total_payments'] = $this->payments()->sum('amount');
        $this->saveQuietly();

        $this->refresh();

        if ($this->status === InvoiceStatus::POSTED && $this->balance_due == 0) {
            $this->status = InvoiceStatus::PAID;
            $this->saveQuietly();
        } elseif ($this->status === InvoiceStatus::PAID && $this->balance_due != 0) {
            $this->status = InvoiceStatus::POSTED;
            $this->saveQuietly();
        }
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [InvoiceStatus::POSTED, InvoiceStatus::PAID, InvoiceStatus::VOID]);
    }
}
