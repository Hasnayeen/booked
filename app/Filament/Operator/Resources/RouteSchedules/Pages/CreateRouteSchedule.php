<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\RouteSchedules\Pages;

use App\Filament\Operator\Resources\RouteSchedules\RouteScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRouteSchedule extends CreateRecord
{
    protected static string $resource = RouteScheduleResource::class;
}
