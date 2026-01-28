<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    public $fillable = ['first_name', 'last_name', 'email', 'phone'];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }

    public function invoices(): Builder
    {
        return Invoice::whereIn('client_id', $this->clients()->pluck('clients.id'));
    }
}
