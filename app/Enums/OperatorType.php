<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OperatorType: string implements HasLabel, HasIcon, HasColor
{
    case Hotel = 'hotel';
    case Bus = 'bus';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hotel => 'Hotel',
            self::Bus => 'Bus',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Hotel => 'lucide-hotel',
            self::Bus => 'lucide-bus',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Hotel => 'primary',
            self::Bus => 'info',
        };
    }
}
