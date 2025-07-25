<?php

namespace App\Filament\Admin\Resources\Operators\Pages;

use App\Filament\Admin\Resources\Operators\OperatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOperators extends ListRecords
{
    protected static string $resource = OperatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
