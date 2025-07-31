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
                TextEntry::make('operator.name')
                    ->numeric(),
                TextEntry::make('bus.id')
                    ->numeric(),
                TextEntry::make('route_name'),
                TextEntry::make('origin_city'),
                TextEntry::make('destination_city'),
                TextEntry::make('departure_time')
                    ->time(),
                TextEntry::make('arrival_time')
                    ->time(),
                TextEntry::make('distance_km')
                    ->numeric(),
                TextEntry::make('estimated_duration')
                    ->time(),
                TextEntry::make('base_price')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
