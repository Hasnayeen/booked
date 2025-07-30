<?php

namespace App\Filament\Operator\Resources\Buses;

use App\Filament\Operator\Resources\Buses\Pages\CreateBus;
use App\Filament\Operator\Resources\Buses\Pages\EditBus;
use App\Filament\Operator\Resources\Buses\Pages\ListBuses;
use App\Filament\Operator\Resources\Buses\Pages\ViewBus;
use App\Filament\Operator\Resources\Buses\Schemas\BusForm;
use App\Filament\Operator\Resources\Buses\Schemas\BusInfolist;
use App\Filament\Operator\Resources\Buses\Tables\BusesTable;
use App\Models\Bus;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static ?string $recordTitleAttribute = 'bus_number';

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Bus;

    protected static string|UnitEnum|null $navigationGroup = 'Fleet Management';

    public static function form(Schema $schema): Schema
    {
        return BusForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusesTable::configure($table);
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

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
