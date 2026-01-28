<?php

namespace App\Exceptions;

use Exception;

class InvoiceLockedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot modify a posted, void, or paid invoice.');
    }
}
