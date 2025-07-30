<?php

namespace App\Filament\Operator\Resources\Buses\Pages;

use App\Filament\Operator\Resources\Buses\BusResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBus extends CreateRecord
{
    protected static string $resource = BusResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operator_id'] = Auth::user()->operators()->first()?->id;
        dd($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
