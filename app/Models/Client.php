<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    protected function primaryContact(): Attribute
    {
        return Attribute::get(fn () => $this->contacts()->wherePivot('is_primary', true)->first());
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }

    public function secondaryContacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->wherePivot('is_primary', false)
            ->withPivot('is_primary');
    }
}
