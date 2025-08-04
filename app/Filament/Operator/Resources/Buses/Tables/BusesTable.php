<?php

declare(strict_types=1);

namespace App\Filament\Operator\Resources\Buses\Tables;

use App\Enums\BusCategory;
use App\Enums\BusType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bus_number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->searchable(),

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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
