<?php

namespace App\Filament\Guest\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class Home extends Page
{
    protected string $view = 'filament.home.pages.home';

    protected static ?string $slug = '/';

    protected ?string $heading = '';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('search_tabs')
                    ->contained(false)
                    ->tabs([
                        Tab::make('bus')
                            ->label('Bus')
                            ->columns(2)
                            ->schema([
                                TextInput::make('from')
                                    ->required()
                                    ->placeholder('Enter departure location'),
                                TextInput::make('to')
                                    ->required()
                                    ->placeholder('Enter destination location'),
                                DatePicker::make('date')
                                    ->required()
                                    ->placeholder('Select travel date'),
                                Select::make('passengers')
                                    ->required()
                                    ->options([
                                        '1' => '1 Passenger',
                                        '2' => '2 Passengers',
                                        '3' => '3 Passengers',
                                        '4' => '4 Passengers',
                                        '5' => '5 Passengers',
                                    ])
                                    ->placeholder('Select number of passengers'),
                            ]),

                        Tab::make('hotel')
                            ->label('Hotel')
                            ->schema([]),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $this->validate();

        return redirect()->route('filament.home.pages.search', $this->data);
    }
}
