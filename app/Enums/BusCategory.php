<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BusCategory: string implements HasColor, HasIcon, HasLabel
{
    case Economy = 'economy';
    case LUXURY = 'luxury';
    case SLEEPER = 'sleeper';
    case BUSINESS = 'business';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Economy => 'Economy',
            self::LUXURY => 'Luxury',
            self::SLEEPER => 'Sleeper',
            self::BUSINESS => 'Business',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Economy => 'lucide-coins',
            self::LUXURY => 'lucide-gem',
            self::SLEEPER => 'lucide-bed',
            self::BUSINESS => 'lucide-briefcase-business',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Economy => 'gray',
            self::LUXURY => 'danger',
            self::SLEEPER => 'info',
            self::BUSINESS => 'success',
        };
    }
}
