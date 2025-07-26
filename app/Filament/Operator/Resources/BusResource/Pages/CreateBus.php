<?php

namespace App\Filament\Operator\Resources\BusResource\Pages;

use App\Filament\Operator\Resources\BusResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBus extends CreateRecord
{
    protected static string $resource = BusResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the operator_id to the current user's operator
        $data['operator_id'] = Auth::user()->operators()->first()?->id;
        // dd($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
