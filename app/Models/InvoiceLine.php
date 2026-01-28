<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Exceptions\InvoiceLockedException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id',
        'sku',
        'name',
        'description',
        'school_year',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => MoneyCast::class,
        'amount' => MoneyCast::class,
    ];

    public static function booted(): void
    {
        static::saving(function (InvoiceLine $line) {
            if ($line->invoice->isLocked()) {
                throw new InvoiceLockedException;
            }

            $line->amount = $line->unit_price * $line->quantity;
        });

        static::saved(function (InvoiceLine $line) {
            $line->invoice->recalculateTotal();
        });

        static::deleting(function (InvoiceLine $line) {
            if ($line->invoice->isLocked()) {
                throw new InvoiceLockedException;
            }
        });

        static::deleted(function (InvoiceLine $line) {
            $line->invoice->recalculateTotal();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function schoolYearFormatted(): Attribute
    {
        return Attribute::get(fn () => ($this->school_year - 1).'-'.$this->school_year);
    }
}
