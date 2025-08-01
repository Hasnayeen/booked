<?php

namespace App\Livewire;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

class HomePageSearch extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount()
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

        return redirect()->route('search.results', $this->data);
    }

    public function render()
    {
        return view('livewire.home-page-search');
    }
}
