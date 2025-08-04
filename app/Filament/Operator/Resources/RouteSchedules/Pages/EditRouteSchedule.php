<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\RouteSchedules\Pages;

use App\Filament\Operator\Resources\RouteSchedules\RouteScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRouteSchedule extends EditRecord
{
    protected static string $resource = RouteScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
