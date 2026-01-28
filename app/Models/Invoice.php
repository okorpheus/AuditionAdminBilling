<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $casts = [
        'total' => MoneyCast::class,
        'status' => InvoiceStatus::class,
        'invoice_date' => 'date',
        'due_date' => 'date',
        'date_sent' => 'date',
    ];

    public function formattedTotal(): string
    {
        return '$'.number_format($this->total, 2);
    }
}
