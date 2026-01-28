<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    public $fillable = ['first_name', 'last_name', 'email', 'phone'];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }
}
