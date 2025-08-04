<?php

namespace App\Filament\Guest\Pages;

use App\Models\Route;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
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

    public string $search_type = 'bus';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('search_tabs')
                    ->contained(false)
                    ->tabs([
                        Tab::make('Bus')
                            ->columns(2)
                            ->schema([
                                TextInput::make('from')
                                    ->required(fn () => $this->search_type === 'bus')
                                    ->datalist(Route::distinct('origin_city')->pluck('origin_city'))
                                    ->placeholder('Enter departure location'),
                                TextInput::make('to')
                                    ->required(fn () => $this->search_type === 'bus')
                                    ->datalist(Route::distinct('destination_city')->pluck('destination_city'))
                                    ->placeholder('Enter destination location'),
                                DatePicker::make('date')
                                    ->required(fn () => $this->search_type === 'bus')
                                    ->placeholder('Select travel date'),
                                Select::make('passengers')
                                    ->required(fn () => $this->search_type === 'bus')
                                    ->default('1')
                                    ->options(array_map(
                                        fn($i) => "$i Passenger" . ($i > 1 ? 's' : ''),
                                        range(1, 4)
                                    ))
                                    ->placeholder('Select number of passengers'),
                                Grid::make()
                                    ->columnSpanFull()
                                    ->columns(1)
                                    ->schema([
                                        Action::make('Search')
                                            ->color('primary')
                                            ->extraAttributes(['class' => 'w-full'])
                                            ->action(fn () => $this->submit('bus')),
                                    ])
                            ]),
                        Tab::make('Hotel')
                            ->columns(2)
                            ->schema([
                                TextInput::make('city')
                                    ->required(fn () => $this->search_type === 'hotel')
                                    ->datalist(Route::distinct('destination_city')->pluck('destination_city'))
                                    ->placeholder('Enter city or area'),
                                Select::make('guests')
                                    ->required(fn () => $this->search_type === 'hotel')
                                    ->default('1')
                                    ->options(array_map(
                                        fn($i) => "$i Guest" . ($i > 1 ? 's' : ''),
                                        range(1, 10)
                                    ))
                                    ->placeholder('Select number of guests'),
                                DatePicker::make('check_in')
                                    ->required(fn () => $this->search_type === 'hotel')
                                    ->placeholder('Select check-in date'),
                                DatePicker::make('check_out')
                                    ->required(fn () => $this->search_type === 'hotel')
                                    ->placeholder('Select check-out date'),
                                Grid::make()
                                    ->columnSpanFull()
                                    ->columns(1)
                                    ->schema([
                                        Action::make('Search')
                                            ->color('primary')
                                            ->extraAttributes(['class' => 'w-full'])
                                            ->action(fn () => $this->submit('hotel')),
                                    ])
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit($activeTab)
    {
        $this->search_type = $activeTab;
        $this->validate();

        return redirect()->route('filament.guest.pages.search', ['search_type' => $this->search_type, ...$this->data]);
    }
}
