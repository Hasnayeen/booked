<?php

namespace App\Filament\Operator\Resources\RouteSchedules;

use App\Filament\Operator\Resources\RouteSchedules\Pages\CreateRouteSchedule;
use App\Filament\Operator\Resources\RouteSchedules\Pages\EditRouteSchedule;
use App\Filament\Operator\Resources\RouteSchedules\Pages\ListRouteSchedules;
use App\Filament\Operator\Resources\RouteSchedules\Pages\ViewRouteSchedule;
use App\Filament\Operator\Resources\RouteSchedules\Schemas\RouteScheduleForm;
use App\Filament\Operator\Resources\RouteSchedules\Schemas\RouteScheduleInfolist;
use App\Filament\Operator\Resources\RouteSchedules\Tables\RouteSchedulesTable;
use App\Models\RouteSchedule;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RouteScheduleResource extends Resource
{
    protected static ?string $model = RouteSchedule::class;

    protected static ?string $navigationLabel = 'Schedules';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::CalendarDays;

    protected static ?string $recordTitleAttribute = 'route.route_name';

    protected static string|UnitEnum|null $navigationGroup = 'Fleet Management';

    public static function form(Schema $schema): Schema
    {
        return RouteScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RouteScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RouteSchedulesTable::configure($table);
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
            'index' => ListRouteSchedules::route('/'),
            'create' => CreateRouteSchedule::route('/create'),
            'view' => ViewRouteSchedule::route('/{record}'),
            'edit' => EditRouteSchedule::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->whereHas('route', function (Builder $query): void {
                $query->where('operator_id', filament()->getTenant()->id);
            })
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
