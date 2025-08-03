<?php

namespace App\Filament\Operator\Resources\Bookings;

use App\Filament\Operator\Resources\Bookings\Pages\ListBookings;
use App\Filament\Operator\Resources\Bookings\Pages\ViewBooking;
use App\Filament\Operator\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Operator\Resources\Bookings\Schemas\BookingInfolist;
use App\Filament\Operator\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Ticket;

    protected static ?string $recordTitleAttribute = 'customer_name';

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
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
            'index' => ListBookings::route('/'),
            'view' => ViewBooking::route('/{record}'),
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
