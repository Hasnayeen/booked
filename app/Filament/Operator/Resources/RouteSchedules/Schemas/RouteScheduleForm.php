<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\RouteSchedules\Schemas;

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

class RouteScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Grid::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make('Schedule Information')
                            ->collapsible()
                            ->schema([
                                Select::make('route_id')
                                    ->relationship('route', 'route_name', fn ($query) => $query->where('operator_id', filament()->getTenant()->id)
                                        ->where('is_active', true))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select the route for this schedule'),

                                Select::make('bus_id')
                                    ->relationship('bus', 'bus_number', fn ($query) => $query->where('operator_id', filament()->getTenant()->id)
                                        ->where('is_active', true))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select the bus assigned to this schedule'),

                                Toggle::make('is_active')
                                    ->inline(false)
                                    ->default(true)
                                    ->helperText('Set whether this schedule is currently active'),

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

                                TextInput::make('base_fare')
                                    ->required()
                                    ->numeric()
                                    ->suffix('¢')
                                    ->helperText('Base fare in cents (e.g., 2500 = $25.00)')
                                    ->columnSpanFull(),

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
                                    ->helperText('Add days or specific dates when this schedule is not operational')
                                    ->addActionLabel('Add Off Day')
                                    ->defaultItems(0),
                            ])
                            ->columnSpanFull(),

                        Section::make('Additional Information')
                            ->schema([
                                KeyValue::make('metadata')
                                    ->helperText('Add any additional schedule information as key-value pairs')
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

            // Handle overnight schedules (arrival next day)
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
}
