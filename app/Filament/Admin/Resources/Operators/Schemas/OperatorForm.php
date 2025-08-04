<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Operators\Schemas;

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OperatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('type')
                    ->options(OperatorType::class)
                    ->required()
                    ->columnSpan(1),

                Select::make('status')
                    ->options(OperatorStatus::class)
                    ->default(OperatorStatus::PENDING->value)
                    ->required()
                    ->visible(fn (): bool => Auth::user()->roles()->whereHas('permissions', function (Builder $query): void {
                        $query->where('name', 'approve_operator');
                    })->exists(),
                    )
                    ->columnSpan(1),

                TextInput::make('contact_email')
                    ->label('Contact Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('contact_phone')
                    ->label('Contact Phone')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('+1 (555) 123-4567')
                    ->columnSpan(1),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Brief description of the operator...'),

                Textarea::make('metadata')
                    ->rows(3)
                    ->placeholder('Additional JSON metadata...')
                    ->helperText('Store additional information as JSON')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
