<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Model;

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
}
