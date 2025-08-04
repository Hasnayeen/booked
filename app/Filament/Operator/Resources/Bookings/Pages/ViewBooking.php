<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\Bookings\Pages;

use App\Filament\Operator\Resources\Bookings\BookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
