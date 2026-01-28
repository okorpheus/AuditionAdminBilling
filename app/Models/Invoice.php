<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
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
        'total' => MoneyCast::class,
        'status' => InvoiceStatus::class,
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function formattedTotal(): string
    {
        return '$'.number_format($this->total, 2);
    }

    public function recalculateTotal(): void
    {
        $this->total = $this->lines()->sum('amount');
        $this->saveQuietly();
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [InvoiceStatus::POSTED, InvoiceStatus::PAID, InvoiceStatus::VOID]);
    }
}
