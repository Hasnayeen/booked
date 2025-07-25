<?php

namespace App\Enums;

enum OperatorType: string
{
    case HOTEL = 'hotel';
    case BUS = 'bus';

    public function label(): string
    {
        return match ($this) {
            self::HOTEL => 'Hotel',
            self::BUS => 'Bus',
        };
    }
}
