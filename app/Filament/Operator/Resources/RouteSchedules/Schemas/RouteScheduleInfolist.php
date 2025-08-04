<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\RouteSchedules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RouteScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('route.route_name')
                    ->label('Route'),

                TextEntry::make('route.origin_city')
                    ->label('Origin'),

                TextEntry::make('route.destination_city')
                    ->label('Destination'),

                TextEntry::make('bus.bus_number')
                    ->label('Bus Number'),

                TextEntry::make('bus.category')
                    ->label('Bus Category'),

                TextEntry::make('bus.type')
                    ->label('Bus Type'),

                TextEntry::make('departure_time')
                    ->time('H:i')
                    ->label('Departure Time'),

                TextEntry::make('arrival_time')
                    ->time('H:i')
                    ->label('Arrival Time'),

                TextEntry::make('estimated_duration')
                    ->label('Duration')
                    ->placeholder('—'),

                TextEntry::make('base_fare')
                    ->label('Base Fare')
                    ->money('USD', divideBy: 100),

                IconEntry::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextEntry::make('off_days')
                    ->label('Off Days')
                    ->listWithLineBreaks()
                    ->getStateUsing(fn ($record) => collect($record->off_days ?? [])->pluck('value')->toArray())
                    ->placeholder('No off days'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Created'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated'),
            ]);
    }
}
