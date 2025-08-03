<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ListBusBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getTableQuery(): Builder|Relation|null
    {
        return static::getResource()::getEloquentQuery()
            ->where('type', 'bus');
    }
}
