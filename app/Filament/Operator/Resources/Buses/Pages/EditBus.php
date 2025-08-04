<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\Buses\Pages;

use App\Filament\Operator\Resources\Buses\BusResource;
use App\ValueObjects\SeatConfiguration;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use InvalidArgumentException;

class EditBus extends EditRecord
{
    protected static string $resource = BusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert seat_config back to form data for editing
        if (isset($data['seat_config']) && $data['seat_config'] instanceof SeatConfiguration) {
            $formData = $data['seat_config']->toFormData();
            $data = array_merge($data, $formData);
        } elseif (isset($data['seat_config']) && is_array($data['seat_config'])) {
            // Handle case where seat_config is already an array (from JSON)
            try {
                $seatConfig = SeatConfiguration::fromArray($data['seat_config']);
                $formData = $seatConfig->toFormData();
                $data = array_merge($data, $formData);
            } catch (Exception $e) {
                logger()->error('Failed to convert seat config array to form data', [
                    'data' => $data['seat_config'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
        unset($data['seat_config']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Process seat configuration data only if seat configuration fields are provided
        $seatConfigFields = ['deck', 'seat_type', 'total_columns', 'column_label', 'column_layout', 'total_rows', 'row_label', 'price_per_seat'];
        $hasSeatConfigData = array_intersect_key($data, array_flip($seatConfigFields));

        // Check if the current record has seat_config or if new seat config data is provided
        $currentHasSeatConfig = $this->record->seat_config !== null;

        if ($hasSeatConfigData !== [] || $currentHasSeatConfig) {
            try {
                $seatConfig = SeatConfiguration::fromFormData($data);
                $data['seat_config'] = $seatConfig;

                // Ensure total_seats matches the calculated seats from configuration
                $calculatedSeats = $seatConfig->getTotalSeats();
                $data['total_seats'] = $calculatedSeats;
            } catch (InvalidArgumentException $e) {
                logger()->error('Invalid seat configuration data during update', [
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
