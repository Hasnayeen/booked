<?php

namespace App\Filament\Operator\Resources\Routes\Schemas;

use Carbon\Carbon;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Grid::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make('Route Information')
                            ->collapsible()
                            ->schema([
                                Select::make('bus_id')
                                    ->relationship('bus', 'bus_number')
                                    ->helperText('Select the bus assigned to this route'),

                                Toggle::make('is_active')
                                    ->inline(false)
                                    ->default(true)
                                    ->helperText('Set whether this route is currently active'),

                                Fieldset::make('Route Details')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextInput::make('origin_city')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->helperText('Departure city')
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                self::generateRouteName($set, $get);
                                            }),

                                        TextInput::make('destination_city')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->helperText('Arrival city')
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                self::generateRouteName($set, $get);
                                            }),

                                        TextInput::make('route_name')
                                            ->columnSpanFull()
                                            ->required()
                                            ->maxLength(255)
                                            ->helperText('Route name (will be auto-generated if left empty)'),
                                    ]),

                                Fieldset::make('Schedule Details')
                                    ->columnSpanFull()
                                    ->schema([
                                        TimePicker::make('departure_time')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->seconds(false)
                                            ->helperText('Scheduled departure time')
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                self::calculateEstimatedDuration($set, $get);
                                            }),

                                        TimePicker::make('arrival_time')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->seconds(false)
                                            ->helperText('Scheduled arrival time')
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                self::calculateEstimatedDuration($set, $get);
                                            }),

                                        TextInput::make('estimated_duration')
                                            ->readOnly()
                                            ->placeholder('HH:MM')
                                            ->helperText('Automatically calculated from departure and arrival times'),
                                    ]),

                                TextInput::make('distance_km')
                                    ->numeric()
                                    ->suffix('km')
                                    ->helperText('Distance in kilometers'),

                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Grid::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Schedule & Availability')
                            ->schema([
                                Repeater::make('off_days')
                                    ->schema([
                                        Select::make('type')
                                            ->options([
                                                'day' => 'Weekday',
                                                'date' => 'Specific Date',
                                            ])
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn ($set) => $set('value', null)),

                                        Select::make('value')
                                            ->options(fn (Get $get): array => match ($get('type')) {
                                                'day' => [
                                                    'monday' => 'Monday',
                                                    'tuesday' => 'Tuesday',
                                                    'wednesday' => 'Wednesday',
                                                    'thursday' => 'Thursday',
                                                    'friday' => 'Friday',
                                                    'saturday' => 'Saturday',
                                                    'sunday' => 'Sunday',
                                                ],
                                                'date' => [],
                                                default => [],
                                            })
                                            ->visible(fn (Get $get): bool => $get('type') === 'day')
                                            ->placeholder('Select day')
                                            ->required(fn (Get $get): bool => $get('type') === 'day'),

                                        DatePicker::make('value')
                                            ->visible(fn (Get $get): bool => $get('type') === 'date')
                                            ->placeholder('Select date')
                                            ->required(fn (Get $get): bool => $get('type') === 'date'),
                                    ])
                                    ->columns(2)
                                    ->helperText('Add days or specific dates when this route is not available')
                                    ->addActionLabel('Add Off Day')
                                    ->defaultItems(0),
                            ])
                            ->columnSpanFull(),

                        Section::make('Stops & Points')
                            ->schema([
                                Repeater::make('stops')
                                    ->schema([
                                        TextInput::make('stop')
                                            ->label('Stop')
                                            ->placeholder('e.g., Central Station, Main Square')
                                            ->maxLength(255)
                                            ->required(),

                                        TimePicker::make('time')
                                            ->label('Time')
                                            ->seconds(false)
                                            ->placeholder('HH:MM')
                                            ->helperText('Arrival/departure time at this stop'),
                                    ])
                                    ->columns(2)
                                    ->helperText('Add intermediate stops along the route with their scheduled times')
                                    ->addActionLabel('Add Stop')
                                    ->defaultItems(0),

                                Repeater::make('boarding_points')
                                    ->schema([
                                        TextInput::make('point')
                                            ->label('Boarding Point')
                                            ->placeholder('e.g., Terminal A, Gate 5')
                                            ->maxLength(255)
                                            ->required(),

                                        TimePicker::make('time')
                                            ->label('Time')
                                            ->seconds(false)
                                            ->placeholder('HH:MM')
                                            ->helperText('Boarding time at this point'),
                                    ])
                                    ->columns(2)
                                    ->helperText('Add passenger boarding locations with their scheduled times')
                                    ->addActionLabel('Add Boarding Point')
                                    ->defaultItems(0),

                                Repeater::make('drop_off_points')
                                    ->schema([
                                        TextInput::make('point')
                                            ->label('Drop-off Point')
                                            ->placeholder('e.g., Terminal B, Station Exit')
                                            ->maxLength(255)
                                            ->required(),

                                        TimePicker::make('time')
                                            ->label('Time')
                                            ->seconds(false)
                                            ->placeholder('HH:MM')
                                            ->helperText('Drop-off time at this point'),
                                    ])
                                    ->columns(2)
                                    ->helperText('Add passenger drop-off locations with their scheduled times')
                                    ->addActionLabel('Add Drop-off Point')
                                    ->defaultItems(0),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),

                        Section::make('Additional Information')
                            ->schema([
                                KeyValue::make('metadata')
                                    ->helperText('Add any additional route information as key-value pairs')
                                    ->addActionLabel('Add Information'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function calculateEstimatedDuration(Set $set, Get $get): void
    {
        $departureTime = $get('departure_time');
        $arrivalTime = $get('arrival_time');

        if (! $departureTime || ! $arrivalTime) {
            $set('estimated_duration', null);

            return;
        }

        try {
            $departure = Carbon::createFromFormat('H:i', $departureTime);
            $arrival = Carbon::createFromFormat('H:i', $arrivalTime);

            // Handle overnight routes (arrival next day)
            if ($arrival->lessThan($departure)) {
                $arrival->addDay();
            }

            $duration = $arrival->diff($departure);
            $estimatedDuration = sprintf('%02d:%02d', $duration->h, $duration->i);

            $set('estimated_duration', $estimatedDuration);
        } catch (Exception) {
            $set('estimated_duration', null);
        }
    }

    private static function generateRouteName(Set $set, Get $get): void
    {
        $originCity = $get('origin_city');
        $destinationCity = $get('destination_city');
        $routeName = $get('route_name');

        // Only auto-generate if route_name is empty
        if ($routeName) {
            return;
        }

        if (! $originCity || ! $destinationCity) {
            return;
        }

        // Generate route name in format: "Origin to Destination"
        $autoRouteName = trim((string) $originCity) . ' to ' . trim((string) $destinationCity);
        $set('route_name', $autoRouteName);
    }
}
