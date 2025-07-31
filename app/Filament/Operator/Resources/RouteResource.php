<?php

namespace App\Filament\Operator\Resources;

use App\Filament\Operator\Resources\Routes\Pages\CreateRoute;
use App\Filament\Operator\Resources\Routes\Pages\EditRoute;
use App\Filament\Operator\Resources\Routes\Pages\ListRoutes;
use App\Filament\Operator\Resources\Routes\Pages\ViewRoute;
use App\Filament\Operator\Resources\Routes\Schemas\RouteForm;
use App\Filament\Operator\Resources\Routes\Schemas\RouteInfolist;
use App\Filament\Operator\Resources\Routes\Tables\RoutesTable;
use App\Models\Route;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RouteResource extends Resource
{
    protected static ?string $model = Route::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Route;

    protected static ?string $recordTitleAttribute = 'route_name';

    protected static string|UnitEnum|null $navigationGroup = 'Fleet Management';

    public static function form(Schema $schema): Schema
    {
        return RouteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RouteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoutesTable::configure($table);
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
            'index' => ListRoutes::route('/'),
            'create' => CreateRoute::route('/create'),
            'view' => ViewRoute::route('/{record}'),
            'edit' => EditRoute::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('operator_id', filament()->getTenant()->id);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->where('operator_id', filament()->getTenant()->id)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
