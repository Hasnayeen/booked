<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\Routes\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
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
                        Section::make('Stops & Points')
                            ->schema([
                                Repeater::make('stops')
                                    ->schema([
                                        TextInput::make('stop')
                                            ->label('Stop')
                                            ->placeholder('e.g., Central Station, Main Square')
                                            ->maxLength(255)
                                            ->required(),
                                    ])
                                    ->columns(1)
                                    ->helperText('Add intermediate stops along the route')
                                    ->addActionLabel('Add Stop')
                                    ->defaultItems(0),

                                Repeater::make('boarding_points')
                                    ->schema([
                                        TextInput::make('point')
                                            ->label('Boarding Point')
                                            ->placeholder('e.g., Terminal A, Gate 5')
                                            ->maxLength(255)
                                            ->required(),
                                    ])
                                    ->columns(1)
                                    ->helperText('Add passenger boarding locations')
                                    ->addActionLabel('Add Boarding Point')
                                    ->defaultItems(0),

                                Repeater::make('drop_off_points')
                                    ->schema([
                                        TextInput::make('point')
                                            ->label('Drop-off Point')
                                            ->placeholder('e.g., Terminal B, Station Exit')
                                            ->maxLength(255)
                                            ->required(),
                                    ])
                                    ->columns(1)
                                    ->helperText('Add passenger drop-off locations')
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
