<?php

namespace App\Filament\Operator\Resources\RouteSchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RouteSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('route.route_name')
                    ->label('Route')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): string => $record->route->origin_city . ' → ' . $record->route->destination_city),

                TextColumn::make('bus.bus_number')
                    ->label('Bus')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): string => $record->bus->category . ' • ' . $record->bus->type),

                TextColumn::make('departure_time')
                    ->label('Departure')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('arrival_time')
                    ->label('Arrival')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('estimated_duration')
                    ->label('Duration')
                    ->getStateUsing(fn ($record) => $record->estimated_duration)
                    ->placeholder('—'),

                TextColumn::make('base_fare')
                    ->label('Base Fare')
                    ->money('USD', divideBy: 100)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
