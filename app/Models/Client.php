<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
        'audition_date',
        'status',
    ];

    protected $casts = [
        'audition_date' => 'date',
        'status' => ClientStatus::class,
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->withPivot('is_primary');
    }

    public function primaryContact(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->wherePivot('is_primary', true)
            ->withPivot('is_primary');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function secondaryContacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->wherePivot('is_primary', false)
            ->withPivot('is_primary');
    }
}
