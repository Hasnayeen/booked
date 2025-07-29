<?php

namespace App\Filament\Operator\Resources;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Filament\Operator\Resources\BusResource\Pages\CreateBus;
use App\Filament\Operator\Resources\BusResource\Pages\EditBus;
use App\Filament\Operator\Resources\BusResource\Pages\ListBuses;
use App\Filament\Operator\Resources\BusResource\Pages\ViewBus;
use App\Models\Bus;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
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
                    ->columnSpan(3),

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
                    ->columnSpan(2),

                Section::make('Additional Information')
                    ->schema([
                        KeyValue::make('metadata')
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
                TextColumn::make('bus_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Bus Number'),

                TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->label('Category'),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->label('Type'),

                TextColumn::make('total_seats')
                    ->numeric()
                    ->sortable()
                    ->label('Seats'),

                TextColumn::make('license_plate')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('License Plate'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('category')
                    ->options(BusCategory::class)
                    ->label('Category'),

                SelectFilter::make('type')
                    ->options(BusType::class)
                    ->label('Type'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All buses')
                    ->trueLabel('Active buses')
                    ->falseLabel('Inactive buses'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
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
            'index' => ListBuses::route('/'),
            'create' => CreateBus::route('/create'),
            'view' => ViewBus::route('/{record}'),
            'edit' => EditBus::route('/{record}/edit'),
        ];
    }
}
