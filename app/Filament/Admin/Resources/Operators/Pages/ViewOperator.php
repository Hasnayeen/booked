<?php

namespace App\Filament\Admin\Resources\Operators\Pages;

use App\Filament\Admin\Resources\Operators\OperatorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOperator extends ViewRecord
{
    protected static string $resource = OperatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
