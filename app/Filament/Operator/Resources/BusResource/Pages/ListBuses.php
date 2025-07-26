<?php

namespace App\Filament\Operator\Resources\BusResource\Pages;

use App\Filament\Operator\Resources\BusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBuses extends ListRecords
{
    protected static string $resource = BusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
