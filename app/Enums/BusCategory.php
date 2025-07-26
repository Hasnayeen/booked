<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BusCategory: string implements HasColor, HasIcon, HasLabel
{
    case STANDARD = 'standard';
    case LUXURY = 'luxury';
    case SLEEPER = 'sleeper';
    case CUSTOM = 'custom';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::LUXURY => 'Luxury',
            self::SLEEPER => 'Sleeper',
            self::CUSTOM => 'Custom',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::STANDARD => 'heroicon-o-squares-2x2',
            self::LUXURY => 'heroicon-o-star',
            self::SLEEPER => 'heroicon-o-moon',
            self::CUSTOM => 'heroicon-o-cog-6-tooth',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::STANDARD => 'gray',
            self::LUXURY => 'warning',
            self::SLEEPER => 'info',
            self::CUSTOM => 'success',
        };
    }
}
