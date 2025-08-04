<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Operators;

use App\Filament\Admin\Resources\Operators\Pages\CreateOperator;
use App\Filament\Admin\Resources\Operators\Pages\EditOperator;
use App\Filament\Admin\Resources\Operators\Pages\ListOperators;
use App\Filament\Admin\Resources\Operators\Pages\ViewOperator;
use App\Filament\Admin\Resources\Operators\Schemas\OperatorForm;
use App\Filament\Admin\Resources\Operators\Schemas\OperatorInfolist;
use App\Filament\Admin\Resources\Operators\Tables\OperatorsTable;
use App\Models\Operator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OperatorResource extends Resource
{
    protected static ?string $model = Operator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Operators';

    protected static ?string $modelLabel = 'Operator';

    protected static ?string $pluralModelLabel = 'Operators';

    protected static ?int $navigationSort = 2;

    /**
     * Check if the user can access this resource.
     */
    public static function canAccess(): bool
    {
        return Auth::user()->roles()->whereHas('permissions', function (Builder $query): void {
            $query->where('name', 'approve_operator');
        })->exists();
    }

    /**
     * Check if the user can view any record.
     */
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    /**
     * Check if the user can create a record.
     */
    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    /**
     * Check if the user can edit a record.
     */
    public static function canEdit($record): bool
    {
        return static::canAccess();
    }

    /**
     * Check if the user can delete a record.
     */
    public static function canDelete($record): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return OperatorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OperatorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperatorsTable::configure($table);
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
            'index' => ListOperators::route('/'),
            'create' => CreateOperator::route('/create'),
            'view' => ViewOperator::route('/{record}'),
            'edit' => EditOperator::route('/{record}/edit'),
        ];
    }

    /**
     * Get the default table sort.
     */
    public static function getDefaultTableSortColumn(): ?string
    {
        return 'created_at';
    }

    /**
     * Get the default table sort direction.
     */
    public static function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
}
