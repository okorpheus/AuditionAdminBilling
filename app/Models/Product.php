<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'active',
        'sku',
        'name',
        'description',
        'price',
    ];

    protected $casts = [
        'price' => MoneyCast::class,
    ];

    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

}
