<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Operators\Schemas;

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OperatorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->size('lg')
                    ->weight('bold')
                    ->columnSpanFull(),

                TextEntry::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hotel' => 'blue',
                        'restaurant' => 'green',
                        'service' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (OperatorType $state): string => $state->label()),

                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (OperatorStatus $state): string => $state->label()),

                TextEntry::make('contact_email')
                    ->label('Contact Email')
                    ->icon('heroicon-m-envelope')
                    ->copyable(),

                TextEntry::make('contact_phone')
                    ->label('Contact Phone')
                    ->icon('heroicon-m-phone')
                    ->copyable(),

                TextEntry::make('description')
                    ->columnSpanFull()
                    ->placeholder('No description provided'),

                TextEntry::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->since(),

                TextEntry::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->since(),

                TextEntry::make('metadata')
                    ->label('Additional Information')
                    ->placeholder('No additional information')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
