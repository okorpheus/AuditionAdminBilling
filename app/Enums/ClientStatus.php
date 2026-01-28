<?php

namespace App\Enums;

enum ClientStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'zinc',
        };
    }
}
