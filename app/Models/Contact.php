<?php

namespace App\Models;

use App\Casts\PhoneNumberCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    public $fillable = ['first_name', 'last_name', 'email', 'phone'];

    public $casts = [
        'phone' => PhoneNumberCast::class,
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }

    public function invoices(): Builder
    {
        return Invoice::whereIn('client_id', $this->clients()->pluck('clients.id'));
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'].' '.$attributes['last_name'],
        );
    }
}
