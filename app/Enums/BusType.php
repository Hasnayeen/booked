<?php

namespace App\Enums;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BusType: string implements HasColor, HasIcon, HasLabel
{
    case AC = 'ac';
    case NON_AC = 'non_ac';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AC => 'AC',
            self::NON_AC => 'Non AC',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::AC => LucideIcon::AirVent->value,
            self::NON_AC => LucideIcon::Wind->value,
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AC => 'info',
            self::NON_AC => 'gray',
        };
    }
}
