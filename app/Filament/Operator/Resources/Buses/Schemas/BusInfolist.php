<?php

namespace App\Filament\Operator\Resources\Buses\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('operator.name')
                    ->numeric(),
                TextEntry::make('bus_number'),
                TextEntry::make('category'),
                TextEntry::make('type'),
                TextEntry::make('total_seats')
                    ->numeric(),
                TextEntry::make('license_plate'),
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
