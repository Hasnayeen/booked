<?php

namespace App\Filament\Operator\Resources\Routes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RouteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('route_name')
                    ->label('Route Name'),

                TextEntry::make('origin_city')
                    ->label('Origin City'),

                TextEntry::make('destination_city')
                    ->label('Destination City'),

                TextEntry::make('distance_km')
                    ->label('Distance')
                    ->suffix(' km')
                    ->numeric(),

                TextEntry::make('schedules_count')
                    ->label('Total Schedules')
                    ->getStateUsing(fn ($record) => $record->schedules()->count()),

                TextEntry::make('active_schedules_count')
                    ->label('Active Schedules')
                    ->getStateUsing(fn ($record) => $record->activeSchedules()->count()),

                IconEntry::make('is_active')
                    ->boolean()
                    ->label('Route Active'),

                TextEntry::make('stops')
                    ->label('Stops')
                    ->listWithLineBreaks()
                    ->getStateUsing(fn ($record) => collect($record->stops ?? [])->pluck('stop')->toArray())
                    ->placeholder('No stops defined'),

                TextEntry::make('boarding_points')
                    ->label('Boarding Points')
                    ->listWithLineBreaks()
                    ->getStateUsing(fn ($record) => collect($record->boarding_points ?? [])->pluck('point')->toArray())
                    ->placeholder('No boarding points defined'),

                TextEntry::make('drop_off_points')
                    ->label('Drop-off Points')
                    ->listWithLineBreaks()
                    ->getStateUsing(fn ($record) => collect($record->drop_off_points ?? [])->pluck('point')->toArray())
                    ->placeholder('No drop-off points defined'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Created'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated'),
            ]);
    }
}
