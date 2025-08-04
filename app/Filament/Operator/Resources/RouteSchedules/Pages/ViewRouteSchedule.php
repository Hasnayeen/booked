<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\RouteSchedules\Pages;

use App\Filament\Operator\Resources\RouteSchedules\RouteScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRouteSchedule extends ViewRecord
{
    protected static string $resource = RouteScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
