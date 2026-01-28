<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CHECK = 'check';
    case CARD = 'card';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CHECK => 'Check',
            self::CARD => 'Card',
        };
    }
}