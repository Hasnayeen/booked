<?php

namespace App\Filament\Operator\Resources\Buses\Schemas;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Models\Bus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class BusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Grid::make()
                    ->columnSpan(3)
                    ->schema([
                        Section::make('Bus Information')
                            ->schema([
                                TextInput::make('bus_number')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Bus::class, 'bus_number', ignoreRecord: true)
                                    ->helperText('Enter a unique bus number for identification'),

                                Select::make('category')
                                    ->options(BusCategory::class)
                                    ->required()
                                    ->helperText('Select the service category for this bus'),

                                Select::make('type')
                                    ->options(BusType::class)
                                    ->required()
                                    ->helperText('Select if the bus has air conditioning'),

                                TextInput::make('total_seats')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->helperText('Enter the total number of seats (1-100)'),

                                TextInput::make('license_plate')
                                    ->maxLength(255)
                                    ->helperText('Vehicle registration/license plate number'),

                                Toggle::make('is_active')
                                    ->default(true)
                                    ->helperText('Set whether this bus is currently active'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Amenities & Features')
                            ->schema([
                                Repeater::make('amenities')
                                    ->simple(
                                        TextInput::make('amenity')
                                            ->placeholder('e.g., WiFi, AC, USB Charging')
                                            ->maxLength(255),
                                    )
                                    ->helperText('Add amenities and features available in this bus')
                                    ->addActionLabel('Add Amenity')
                                    ->defaultItems(0),
                            ])
                            ->columnSpanFull(),

                        Section::make('Additional Information')
                            ->schema([
                                KeyValue::make('metadata')
                                    ->helperText('Add any additional information as key-value pairs')
                                    ->addActionLabel('Add Information'),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->columnSpanFull(),
                    ]),

                Section::make('Seat Configuration')
                    ->columns(5)
                    ->schema([
                        Grid::make()
                            ->columnSpan(3)
                            ->schema([
                                ToggleButtons::make('deck')
                                    ->live()
                                    ->columnSpan(1)
                                    ->grouped()
                                    ->options([
                                        '1' => 'Single Deck',
                                        '2' => 'Double Deck',
                                    ])
                                    ->default('1')
                                    ->required()
                                    ->helperText('Select the deck type of the bus'),

                                self::getLowerDeckSeatConfig(),

                                self::getUpperDeckSeatConfig(),
                            ]),

                        Tabs::make('seat_layout')
                            ->label('Seat Layout')
                            ->columnSpan(2)
                            ->tabs([
                                Tab::make('Lower Deck')
                                    ->schema([
                                        View::make('filament.schemas.components.bus_seat_layout')
                                            ->columnSpan(2)
                                    ]),

                                Tab::make('Upper Deck')
                                    ->disabled(fn (Get $get) => $get('deck') !== '2')
                                    ->schema([
                                    ]),
                            ])
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getLowerDeckSeatConfig()
    {
        return Fieldset::make('lower_deck')
            ->label('Seat Layout (Lower Deck)')
            ->columnSpanFull()
            ->schema([
                ToggleButtons::make('seat_type')
                    ->columnSpan(1)
                    ->grouped()
                    ->options([
                        '1' => 'Seat',
                        '2' => 'Sleeper',
                    ])
                    ->default('1')
                    ->required()
                    ->helperText('Select the type of seating available in this bus'),

                TextInput::make('total_columns')
                    ->live()
                    ->columnStart(1)
                    ->required()
                    ->integer()
                    ->minValue(2)
                    ->maxValue(4)
                    ->default(4)
                    ->helperText('Number of seat per row (2-4)'),

                ToggleButtons::make('column_label')
                    ->live()
                    ->options([
                        'alpha' => 'Alphabetical (A, B...)',
                        'numeric' => 'Numeric (1, 2...)',
                    ])
                    ->grouped()
                    ->default('alpha')
                    ->required()
                    ->helperText('Select how the columns are labeled'),

                Select::make('column_layout')
                    ->options(fn (Get $get) => match((int) $get('total_columns')) {
                        2 => ['1:1' => '1:1 (Left: 1, Right: 1)'],
                        3 => ['2:1' => '2:1 (Left: 2, Right: 1)', '1:2' => '1:2 (Left: 1, Right: 2)'],
                        4 => ['2:2' => '2:2 (Left: 2, Right: 2)'],
                    })
                    ->default('2:2')
                    ->helperText('Define the number of columns for left and right seating'),

                TextInput::make('total_rows')
                    ->live()
                    ->columnStart(1)
                    ->required()
                    ->integer()
                    ->minValue(5)
                    ->maxValue(10)
                    ->default(5)
                    ->helperText('Number of rows (5-10)'),

                ToggleButtons::make('row_label')
                    ->live()
                    ->options([
                        'alpha' => 'Alphabetical (A, B...)',
                        'numeric' => 'Numeric (1, 2...)',
                    ])
                    ->default('numeric')
                    ->grouped()
                    ->required()
                    ->helperText('Select how the rows are labeled'),
                
                TextInput::make('price_per_seat')
                    ->columnStart(1)
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Price per seat in this configuration'),
            ]);
    }

    public static function getUpperDeckSeatConfig()
    {
        return Fieldset::make('upper_deck')
            ->label('Seat Layout (Upper Deck)')
            ->visible(fn (Get $get) => $get('deck') === '2')
            ->columnSpanFull()
            ->schema([
                ToggleButtons::make('seat_type_upper')
                    ->label('Seat Type')
                    ->columnSpan(1)
                    ->grouped()
                    ->options([
                        '1' => 'Seat',
                        '2' => 'Sleeper',
                    ])
                    ->default('1')
                    ->required()
                    ->helperText('Select the type of seating available in this bus'),

                TextInput::make('total_columns_upper')
                    ->label('Total Columns')
                    ->live()
                    ->columnStart(1)
                    ->required()
                    ->integer()
                    ->minValue(2)
                    ->maxValue(4)
                    ->default(4)
                    ->helperText('Number of seat per row (2-4)'),

                ToggleButtons::make('column_label_upper')
                    ->label('Column Label')
                    ->live()
                    ->options([
                        'alpha' => 'Alphabetical (A, B...)',
                        'numeric' => 'Numeric (1, 2...)',
                    ])
                    ->default('alpha')
                    ->grouped()
                    ->required()
                    ->helperText('Select how the columns are labeled'),

                Select::make('column_layout_upper')
                    ->label('Column Layout')
                    ->options(fn (Get $get) => match((int) $get('total_columns')) {
                        2 => ['1:1' => '1:1 (Left: 1, Right: 1)'],
                        3 => ['2:1' => '2:1 (Left: 2, Right: 1)', '1:2' => '1:2 (Left: 1, Right: 2)'],
                        4 => ['2:2' => '2:2 (Left: 2, Right: 2)'],
                    })
                    ->default('2:2')
                    ->helperText('Define the number of columns for left and right seating'),

                TextInput::make('total_rows_upper')
                    ->label('Total Rows')
                    ->columnStart(1)
                    ->live()
                    ->required()
                    ->integer()
                    ->minValue(5)
                    ->maxValue(10)
                    ->default(5)
                    ->helperText('Number of rows (5-10)'),

                ToggleButtons::make('row_label_upper')
                    ->label('Row Label')
                    ->live()
                    ->options([
                        'alpha' => 'Alphabetical (A, B...)',
                        'numeric' => 'Numeric (1, 2...)',
                    ])
                    ->default('numeric')
                    ->grouped()
                    ->required()
                    ->helperText('Select how the rows are labeled'),

                TextInput::make('price_per_seat_upper')
                    ->label('Price per Seat')
                    ->columnStart(1)
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Price per seat in this configuration'),
            ]);
    }
}
