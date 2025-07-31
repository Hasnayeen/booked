<?php

namespace App\Filament\Operator\Resources\Routes\Pages;

use App\Filament\Operator\Resources\Routes\RouteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoute extends CreateRecord
{
    protected static string $resource = RouteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operator_id'] = filament()->getTenant()->id;
        unset($data['estimated_duration']); // Remove this field as it's calculated

        return $data;
    }
}
