<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public $fillable = ['first_name', 'last_name', 'email', 'phone'];
}
