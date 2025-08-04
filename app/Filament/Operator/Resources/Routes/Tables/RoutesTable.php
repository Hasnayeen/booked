<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\Routes\Tables;

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

class RoutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('route_name')
                    ->label('Route Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('origin_city')
                    ->label('Origin')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('destination_city')
                    ->label('Destination')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('distance_km')
                    ->label('Distance')
                    ->suffix(' km')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('schedules_count')
                    ->label('Schedules')
                    ->counts('schedules')
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
