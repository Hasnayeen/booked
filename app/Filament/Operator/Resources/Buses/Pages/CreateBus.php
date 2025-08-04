<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\Buses\Pages;

use App\Filament\Operator\Resources\Buses\BusResource;
use App\ValueObjects\SeatConfiguration;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class CreateBus extends CreateRecord
{
    protected static string $resource = BusResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the operator_id from the authenticated user's first operator
        $data['operator_id'] = Auth::user()->operators()->first()?->id;

        // Process seat configuration data only if seat configuration fields are provided
        $seatConfigFields = ['deck', 'seat_type', 'total_columns', 'column_label', 'column_layout', 'total_rows', 'row_label', 'price_per_seat'];
        $hasSeatConfigData = array_intersect_key($data, array_flip($seatConfigFields));

        if ($hasSeatConfigData !== []) {
            try {
                $seatConfig = SeatConfiguration::fromFormData($data);
                $data['seat_config'] = $seatConfig;

                // Ensure total_seats matches the calculated seats from configuration
                $calculatedSeats = $seatConfig->getTotalSeats();
                $data['total_seats'] = $calculatedSeats;
            } catch (InvalidArgumentException $e) {
                // Log the error and let validation handle it
                logger()->error('Invalid seat configuration data', [
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]);
            }

            // Remove form-only fields that shouldn't be stored in the database
            $formOnlyFields = [
                'deck', 'seat_type', 'total_columns', 'column_label', 'column_layout',
                'total_rows', 'row_label', 'price_per_seat',
                'seat_type_upper', 'total_columns_upper', 'column_label_upper',
                'column_layout_upper', 'total_rows_upper', 'row_label_upper', 'price_per_seat_upper',
            ];

            foreach ($formOnlyFields as $field) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
