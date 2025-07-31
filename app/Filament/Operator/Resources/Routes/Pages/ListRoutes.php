<?php

namespace App\Filament\Operator\Resources\Routes\Pages;

use App\Filament\Operator\Resources\Routes\RouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoutes extends ListRecords
{
    protected static string $resource = RouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
