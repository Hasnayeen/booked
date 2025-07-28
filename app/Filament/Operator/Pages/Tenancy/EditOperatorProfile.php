<?php

namespace App\Filament\Operator\Pages\Tenancy;

use App\Enums\OperatorType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditOperatorProfile extends EditTenantProfile
{
    /**
     * Get the label for this profile page.
     */
    public static function getLabel(): string
    {
        return 'Operator Profile';
    }

    /**
     * Get the navigation label for this page.
     */
    public static function getNavigationLabel(): string
    {
        return 'Edit Profile';
    }

    /**
     * Define the profile form.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Operator Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('This is the name displayed throughout the system'),

                Select::make('type')
                    ->label('Operator Type')
                    ->required()
                    ->disabled()
                    ->options([
                        OperatorType::HOTEL->value => 'Hotel Operator',
                        OperatorType::BUS->value => 'Bus Operator',
                    ])
                    ->helperText('The type of service you provide'),

                TextInput::make('contact_email')
                    ->label('Contact Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->helperText('Main contact email for your operator account')
                    ->unique(ignorable: fn ($record) => $record),

                TextInput::make('contact_phone')
                    ->label('Contact Phone')
                    ->tel()
                    ->nullable()
                    ->maxLength(20)
                    ->helperText('Contact phone number for your operator'),

                Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull()
                    ->nullable()
                    ->autosize()
                    ->maxLength(1000)
                    ->rows(4)
                    ->helperText('Brief description of your operator services'),
            ]);
    }
}
