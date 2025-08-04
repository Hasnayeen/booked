<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BusCategory: string implements HasColor, HasIcon, HasLabel
{
    case Economy = 'economy';
    case Luxury = 'luxury';
    case Sleeper = 'sleeper';
    case Business = 'business';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Economy => 'Economy',
            self::Luxury => 'Luxury',
            self::Sleeper => 'Sleeper',
            self::Business => 'Business',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Economy => 'lucide-coins',
            self::Luxury => 'lucide-gem',
            self::Sleeper => 'lucide-bed',
            self::Business => 'lucide-briefcase-business',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Economy => 'gray',
            self::Luxury => 'danger',
            self::Sleeper => 'info',
            self::Business => 'success',
        };
    }
}
