<?php

namespace App\Filament\Admin\Resources\Operators\Tables;

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Models\Operator;
use App\Notifications\OperatorStatusUpdate;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OperatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Operator $record): string => $record->contact_email),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (OperatorType $state): string => match ($state) {
                        OperatorType::Bus => 'primary',
                        OperatorType::Hotel => 'success',
                    })
                    ->formatStateUsing(fn (OperatorType $state): string => $state->label()),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (OperatorStatus $state): string => match ($state) {
                        OperatorStatus::Approved => 'success',
                        OperatorStatus::Pending => 'warning',
                        OperatorStatus::Suspended => 'danger',
                        OperatorStatus::Rejected => 'gray',
                    })
                    ->formatStateUsing(fn (OperatorStatus $state): string => $state->label()),

                TextColumn::make('contact_phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OperatorStatus::class)
                    ->placeholder('All statuses'),

                SelectFilter::make('type')
                    ->options(OperatorType::class)
                    ->placeholder('All types'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Operator $record): bool => $record->status === OperatorStatus::Pending &&
                        Auth::user()->roles()->whereHas('permissions', function (Builder $query): void {
                            $query->where('name', 'approve_operator');
                        })->exists(),
                    )
                    ->form([
                        Textarea::make('admin_message')
                            ->label('Welcome Message (Optional)')
                            ->placeholder('Welcome message for the operator...')
                            ->rows(3),
                    ])
                    ->action(function (Operator $record, array $data): void {
                        $oldStatus = $record->status;
                        $record->update(['status' => OperatorStatus::Approved]);

                        // Get all users associated with this operator
                        $operatorUsers = $record->users;

                        foreach ($operatorUsers as $user) {
                            $user->notify(new OperatorStatusUpdate(
                                $record,
                                $oldStatus,
                                $data['admin_message'] ?? null,
                            ));
                        }

                        Notification::make()
                            ->title('Operator Approved')
                            ->body("'{$record->name}' has been approved successfully.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Approve Operator')
                    ->modalDescription('Are you sure you want to approve this operator?'),

                Action::make('suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->visible(fn (Operator $record): bool => $record->status === OperatorStatus::Approved &&
                        Auth::user()->roles()->whereHas('permissions', function (Builder $query): void {
                            $query->where('name', 'approve_operator');
                        })->exists(),
                    )
                    ->form([
                        Textarea::make('admin_message')
                            ->label('Suspension Reason')
                            ->placeholder('Reason for suspension...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Operator $record, array $data): void {
                        $oldStatus = $record->status;
                        $record->update(['status' => OperatorStatus::Suspended]);

                        // Get all users associated with this operator
                        $operatorUsers = $record->users;

                        foreach ($operatorUsers as $user) {
                            $user->notify(new OperatorStatusUpdate(
                                $record,
                                $oldStatus,
                                $data['admin_message'],
                            ));
                        }

                        Notification::make()
                            ->title('Operator Suspended')
                            ->body("'{$record->name}' has been suspended.")
                            ->warning()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Operator')
                    ->modalDescription('Are you sure you want to suspend this operator?'),

                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Operator $record): bool => $record->status === OperatorStatus::Pending &&
                        Auth::user()->roles()->whereHas('permissions', function (Builder $query): void {
                            $query->where('name', 'approve_operator');
                        })->exists(),
                    )
                    ->form([
                        Textarea::make('admin_message')
                            ->label('Rejection Reason')
                            ->placeholder('Reason for rejection...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Operator $record, array $data): void {
                        $oldStatus = $record->status;
                        $record->update(['status' => OperatorStatus::Rejected]);

                        // Get all users associated with this operator
                        $operatorUsers = $record->users;

                        foreach ($operatorUsers as $user) {
                            $user->notify(new OperatorStatusUpdate(
                                $record,
                                $oldStatus,
                                $data['admin_message'],
                            ));
                        }

                        Notification::make()
                            ->title('Operator Rejected')
                            ->body("'{$record->name}' has been rejected.")
                            ->danger()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reject Operator')
                    ->modalDescription('Are you sure you want to reject this operator?'),

                EditAction::make()
                    ->visible(fn (): bool => Auth::user()->roles()->whereHas('permissions', function (Builder $query): void {
                        $query->where('name', 'approve_operator');
                    })->exists(),
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()->roles()->whereHas('permissions', function (Builder $query): void {
                            $query->where('name', 'approve_operator');
                        })->exists(),
                        ),
                ]),
            ]);
    }
}
