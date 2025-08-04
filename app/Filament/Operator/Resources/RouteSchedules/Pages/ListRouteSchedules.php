<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\RouteSchedules\Pages;

use App\Filament\Operator\Resources\RouteSchedules\RouteScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRouteSchedules extends ListRecords
{
    protected static string $resource = RouteScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
