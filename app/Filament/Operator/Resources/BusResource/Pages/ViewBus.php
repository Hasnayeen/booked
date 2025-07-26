<?php

namespace App\Filament\Operator\Resources\BusResource\Pages;

use App\Filament\Operator\Resources\BusResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBus extends ViewRecord
{
    protected static string $resource = BusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
