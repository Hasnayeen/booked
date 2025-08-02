<?php

namespace App\Filament\Home\Pages;

use App\Models\Route;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class Search extends Page
{
    protected string $view = 'filament.home.pages.search';

    protected ?string $heading = '';

    protected static bool $shouldRegisterNavigation = false;

    #[Url]
    public string $from = '';
    #[Url]
    public string $to = '';
    #[Url]
    public string $date = '';
    #[Url]
    public string $passengers = '';

    public function mount()
    {
        $this->form->fill([
            'from' => $this->from,
            'to' => $this->to,
            'date' => $this->date,
            'passengers' => $this->passengers,
        ]);
    }

    public function content(Schema $schema): Schema
    {
        $schema = parent::content($schema);

        return $schema
            ->record(Route::query()
                ->where('origin_city', $this->from)
                ->where('destination_city', $this->to)
                ->orderBy('departure_time')
                ->get()->all())
            ->columns(12)
            ->components([
                Section::make('filters')
                    ->heading('Filters')
                    ->columnSpan(3)
                    ->description('Use the filters below to refine your search results.')
                    ->schema([
                        // Add filter components here, e.g., TextInput, Select, etc.
                    ]),
                Section::make()
                    ->contained(false)
                    ->columnSpan(9)
                    ->schema([
                        RepeatableEntry::make('*')
                            ->hiddenLabel()
                            ->contained(false)
                            ->extraAttributes(['class' => 'gap-4'])
                            ->schema([
                                Section::make()
                                    ->collapsible()
                                    ->icon(fn (Route $record) => 'logo-' . $record->operator->logo)
                                    ->iconSize('lg')
                                    ->heading(fn (Route $record) => $record->operator->name)
                                    ->description(fn (Route $record) => $record->departure_time->format('h:i A') . ' - ' . $record->arrival_time->format('h:i A'))
                                    ->afterHeader([
                                        TextEntry::make('bus.category')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color(fn (Route $record) => $record->bus->category?->getColor() ?? 'gray'),
                                        TextEntry::make('bus.type')
                                            ->hiddenLabel()
                                            ->badge()
                                            ->color(fn (Route $record) => $record->bus->type?->getColor() ?? 'gray'),
                                    ])
                                    ->schema([
                                        TextEntry::make('bus.bus_number')
                                            ->hiddenLabel()
                                            ->label('Bus Number')
                                            ->size('lg')
                                            ->weight('bold'),
                                        Flex::make([
                                            TextEntry::make('origin_city')
                                                ->hiddenLabel()
                                                ->icon(LucideIcon::MapPin)
                                                ->size('lg')
                                                ->grow(false),
                                            View::make('filament.schemas.components.route-duration'),
                                            TextEntry::make('destination_city')
                                                ->hiddenLabel()
                                                ->icon(LucideIcon::MapPin)
                                                ->size('lg')
                                                ->grow(false),
                                        ])->columnSpanFull(),
                                        Flex::make([
                                            TextEntry::make('departure_time')
                                                ->hiddenLabel()
                                                ->dateTime('h:i A, d M'),
                                            TextEntry::make('arrival_time')
                                                ->hiddenLabel()
                                                ->dateTime('h:i A, d M')
                                                ->grow(false),
                                        ])->columnSpanFull(),
                                        TextEntry::make('bus.seats_available')
                                            ->hiddenLabel()
                                            ->label('Seats Available')
                                            ->color('success'),
                                    ])
                                    ->footer([
                                        Flex::make([
                                            TextEntry::make('bus.min_price')
                                                ->hiddenLabel()
                                                ->money('USD', 100)
                                                ->color('primary')
                                                ->size('lg')
                                                ->weight('bold'),
                                            Action::make('book')
                                                ->label('Book Now')
                                                ->icon(LucideIcon::Ticket)
                                                ->outlined()
                                                ->url(fn (Route $record) => $record)
                                                ->openUrlInNewTab()
                                                ->button(),
                                        ])->columnSpanFull()->extraAttributes([
                                            'class' => 'search-result-footer items-center',
                                        ]),
                                    ]),
                            ])
                    ]),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(8)
            ->schema([
                TextInput::make('from')
                    ->columnSpan(2)
                    ->required()
                    ->placeholder('Enter departure location'),
                TextInput::make('to')
                    ->columnSpan(2)
                    ->required()
                    ->placeholder('Enter destination location'),
                DatePicker::make('date')
                    ->columnSpan(2)
                    ->required()
                    ->placeholder('Select travel date'),
                Select::make('passengers')
                    ->columnSpan(2)
                    ->required()
                    ->options([
                        '1' => '1 Passenger',
                        '2' => '2 Passengers',
                        '3' => '3 Passengers',
                        '4' => '4 Passengers',
                        '5' => '5 Passengers',
                    ])
                    ->placeholder('Select number of passengers'),
            ]);
    }

    public function search()
    {
        $this->validate([
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'date' => 'required|date',
            'passengers' => 'required|in:1,2,3,4,5',
        ]);

        dd($this->from, $this->to, $this->date, $this->passengers);
        // You can replace this with actual search logic, such as querying a database or an API.
        // For example, you might want to redirect to a results page:
        // return redirect()->route('search.results', [
        //     'from' => $this->from,
        //     'to' => $this->to,
        //     'date' => $this->date,
        //     'passengers' => $this->passengers,
        // ]);

        // For now, we will just dump the search parameters for demonstration purposes.
        // In a real application, you would replace this with actual search logic.
        return redirect()->route('filament.home.pages.search', [
            'from' => $this->from,
            'to' => $this->to,
            'date' => $this->date,
            'passengers' => $this->passengers,
        ]);
    }
}