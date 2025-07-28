<?php

namespace App\Enums;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BusType: string implements HasColor, HasIcon, HasLabel
{
    case Ac = 'ac';
    case NonAc = 'non_ac';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Ac => 'AC',
            self::NonAc => 'Non AC',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Ac => LucideIcon::AirVent->value,
            self::NonAc => LucideIcon::Wind->value,
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Ac => 'info',
            self::NonAc => 'gray',
        };
    }
}
