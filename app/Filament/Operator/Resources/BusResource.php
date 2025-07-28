<?php

namespace App\Filament\Operator\Resources;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Filament\Operator\Resources\BusResource\Pages;
use App\Models\Bus;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static ?string $recordTitleAttribute = 'bus_number';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Buses';

    protected static ?string $modelLabel = 'Bus';

    protected static ?string $pluralModelLabel = 'Buses';

    protected static string|UnitEnum|null $navigationGroup = 'Fleet Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->schema([
                Schemas\Components\Section::make('Bus Information')
                    ->schema([
                        Forms\Components\TextInput::make('bus_number')
                            ->required()
                            ->maxLength(255)
                            ->unique(Bus::class, 'bus_number', ignoreRecord: true)
                            ->helperText('Enter a unique bus number for identification'),

                        Forms\Components\Select::make('category')
                            ->options(BusCategory::class)
                            ->required()
                            ->helperText('Select the service category for this bus'),

                        Forms\Components\Select::make('type')
                            ->options(BusType::class)
                            ->required()
                            ->helperText('Select if the bus has air conditioning'),

                        Forms\Components\TextInput::make('total_seats')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Enter the total number of seats (1-100)'),

                        Forms\Components\TextInput::make('license_plate')
                            ->maxLength(255)
                            ->helperText('Vehicle registration/license plate number'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Set whether this bus is currently active'),
                    ])
                    ->columns(2)
                    ->columnSpan(3),

                Schemas\Components\Section::make('Amenities & Features')
                    ->schema([
                        Forms\Components\Repeater::make('amenities')
                            ->simple(
                                Forms\Components\TextInput::make('amenity')
                                    ->placeholder('e.g., WiFi, AC, USB Charging')
                                    ->maxLength(255),
                            )
                            ->helperText('Add amenities and features available in this bus')
                            ->addActionLabel('Add Amenity')
                            ->defaultItems(0),
                    ])
                    ->columnSpan(2),

                Schemas\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->helperText('Add any additional information as key-value pairs')
                            ->addActionLabel('Add Information'),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpan(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bus_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Bus Number'),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->label('Category'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->label('Type'),

                Tables\Columns\TextColumn::make('total_seats')
                    ->numeric()
                    ->sortable()
                    ->label('Seats'),

                Tables\Columns\TextColumn::make('license_plate')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('License Plate'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(BusCategory::class)
                    ->label('Category'),

                Tables\Filters\SelectFilter::make('type')
                    ->options(BusType::class)
                    ->label('Type'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All buses')
                    ->trueLabel('Active buses')
                    ->falseLabel('Inactive buses'),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuses::route('/'),
            'create' => Pages\CreateBus::route('/create'),
            'view' => Pages\ViewBus::route('/{record}'),
            'edit' => Pages\EditBus::route('/{record}/edit'),
        ];
    }
}
