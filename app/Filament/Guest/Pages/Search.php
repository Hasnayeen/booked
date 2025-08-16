<?php

declare(strict_types=1);

namespace App\Filament\Guest\Pages;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Filament\Forms\Components\SeatPicker;
use App\Models\Route;
use App\Models\RouteSchedule;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Search extends Page
{
    use WithPagination;

    protected string $view = 'filament.home.pages.search';

    protected ?string $heading = '';

    protected static bool $shouldRegisterNavigation = false;

    #[Url]
    public int $perPage = 10;

    #[Url]
    public string $search_type = 'bus';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $date = '';

    #[Url]
    public string $passengers = '';

    #[Url]
    public array $price_range = [500, 3000];

    #[Url]
    public string $category = '';

    #[Url]
    public string $type = '';

    protected LengthAwarePaginator $results;

    public function mount(): void
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

        $schedules = RouteSchedule::query()
            ->where('is_active', true)
            ->whereHas('route', function ($query): void {
                $query->where('origin_city', $this->from)
                    ->where('destination_city', $this->to)
                    ->where('is_active', true);
            })
            ->with(['route', 'bus', 'operator', 'busBookings' => function ($query): void {
                $query->where('date', $this->date);
            }])
            ->withSum('busBookings', 'passenger_count')
            ->orderBy('departure_time')
            ->paginate($this->perPage);

        $this->results = $schedules;

        return $schema
            ->record($this->results->all())
            ->columns(12)
            ->components([
                $this->getSearchFiltersComponents(),
                $this->getSearchResultsComponents(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->columnSpanFull()
                    ->columns(8)
                    ->visible(fn (): bool => $this->search_type === 'bus')
                    ->schema([
                        TextInput::make('from')
                            ->columnSpan(2)
                            ->datalist(Route::distinct('origin_city')->pluck('origin_city'))
                            ->required()
                            ->placeholder('Enter departure location'),
                        TextInput::make('to')
                            ->columnSpan(2)
                            ->datalist(Route::distinct('destination_city')->pluck('destination_city'))
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
                    ]),
                Grid::make()
                    ->columns(8)
                    ->visible(fn (): bool => $this->search_type === 'hotel')
                    ->schema([
                        TextInput::make('city')
                            ->columnSpan(2)
                            ->datalist(Route::distinct('destination_city')->pluck('destination_city'))
                            ->required()
                            ->placeholder('Enter city or area'),
                        Select::make('guests')
                            ->columnSpan(2)
                            ->required()
                            ->options(array_map(
                                fn ($i): string => "$i Guest" . ($i > 1 ? 's' : ''),
                                range(1, 10),
                            ))
                            ->placeholder('Select number of guests'),
                        DatePicker::make('check_in')
                            ->columnSpan(2)
                            ->required()
                            ->placeholder('Select check-in date'),
                        DatePicker::make('check_out')
                            ->columnSpan(2)
                            ->required()
                            ->placeholder('Select check-out date'),
                    ]),
            ]);
    }

    public function search(): void
    {
        $this->validate([
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'date' => 'required|date',
            'passengers' => 'required|in:1,2,3,4,5',
        ]);

        $this->from = $this->form->getState()['from'];
        $this->to = $this->form->getState()['to'];
        $this->date = $this->form->getState()['date'];
        $this->passengers = $this->form->getState()['passengers'];
    }

    private function getSearchFiltersComponents(): Component
    {
        return Section::make()
            ->heading('Filters')
            ->columnSpan(3)
            ->description('Use the filters below to refine your search results.')
            ->extraAttributes(['class' => 'sticky top-20'])
            ->schema([
                Grid::make()
                    ->visible(fn (): bool => $this->search_type === 'bus')
                    ->schema([
                        Select::make('category')
                            ->label('Bus Category')
                            ->options(collect(BusCategory::cases())->mapWithKeys(fn (BusCategory $category) => [$category->value => $category->getLabel()]))
                            ->placeholder('Select a category')
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Bus Type')
                            ->options(collect(BusType::cases())->mapWithKeys(fn (BusType $type) => [$type->value => $type->getLabel()]))
                            ->placeholder('Select a type')
                            ->searchable()
                            ->columnSpanFull(),
                        Section::make()
                            ->columnSpanFull()
                            ->description('Price range')
                            ->contained(false)
                            ->schema([
                                Slider::make('price_range')
                                    ->hiddenLabel()
                                    ->extraFieldWrapperAttributes(['class' => 'px-5'])
                                    ->range(500, 3000)
                                    ->step(50)
                                    ->decimalPlaces(0)
                                    ->default([500, 2000])
                                    ->tooltips()
                                    ->columnSpanFull(),
                            ]),
                        CheckboxList::make('operators')
                            ->options(
                                $this->results
                                    ->pluck('operator')
                                    ->unique('id')
                                    ->pluck('name', 'id')
                                    ->toArray(),
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private function getSearchResultsComponents(): Component
    {
        return Section::make()
            ->contained(false)
            ->columnSpan(9)
            ->extraAttributes(['class' => 'search-results'])
            ->schema([
                Section::make()
                    ->visible(fn (array $record): bool => count($record) === 0)
                    ->schema([
                        TextEntry::make('no_results')
                            ->hiddenLabel()
                            ->alignCenter()
                            ->size('lg')
                            ->weight('bold')
                            ->state('0 results found'),
                    ]),
                RepeatableEntry::make('*')
                    ->hiddenLabel()
                    ->contained(false)
                    ->extraAttributes(['class' => 'gap-4 [&_.fi-section-header-description]:font-bold [&_.fi-section-header-description]:text-primary-600'])
                    ->schema([
                        Section::make()
                            ->collapsed()
                            ->collapsible()
                            ->icon(fn (RouteSchedule $record): string => $record->operator->logo ? 'logo-' . $record->operator->logo : 'lucide-triangle-alert')
                            ->iconSize('lg')
                            ->heading(fn (RouteSchedule $record) => $record->operator->name)
                            ->description(fn (RouteSchedule $record): string => $record->departure_time?->format('h:i A') . ' - ' . $record->arrival_time?->format('h:i A'))
                            ->afterHeader([
                                TextEntry::make('bus.category')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color(fn (RouteSchedule $record) => $record->bus?->category?->getColor() ?? 'gray'),
                                TextEntry::make('bus.type')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color(fn (RouteSchedule $record) => $record->bus?->type?->getColor() ?? 'gray'),
                            ])
                            ->schema([
                                TextEntry::make('bus.bus_number')
                                    ->hiddenLabel()
                                    ->label('Bus Number')
                                    ->size('lg')
                                    ->weight('bold'),
                                Flex::make([
                                    TextEntry::make('route.origin_city')
                                        ->hiddenLabel()
                                        ->icon(LucideIcon::MapPin)
                                        ->size('lg')
                                        ->grow(false),
                                    View::make('filament.schemas.components.route-duration'),
                                    TextEntry::make('route.destination_city')
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
                                    Flex::make([
                                        TextEntry::make('bus.all_prices')
                                            ->hiddenLabel()
                                            ->color('primary')
                                            ->size('lg')
                                            ->weight('bold')
                                            ->state(fn ($record): array => array_map(fn ($price): float|int => $price * (int) $this->passengers, $record->bus?->all_prices ?? []))
                                            ->money('USD', 100)
                                            ->listWithLineBreaks()
                                            ->grow(false),
                                        TextEntry::make('bus.all_prices')
                                            ->hiddenLabel()
                                            ->size('md')
                                            ->money('USD', 100)
                                            ->listWithLineBreaks()
                                            ->extraAttributes(['class' => 'text-gray-600 [&_li]:text-gray-600'])
                                            ->grow(false),
                                        TextEntry::make('bus.all_prices')
                                            ->hiddenLabel()
                                            ->size('sm')
                                            ->extraAttributes(['class' => 'text-gray-600'])
                                            ->state('/  Seat')
                                            ->listWithLineBreaks(),
                                    ])->extraAttributes(['class' => 'items-center']),
                                    Flex::make([
                                        TextEntry::make('bus.seats_available')
                                            ->hiddenLabel()
                                            ->size('lg')
                                            ->weight('bold')
                                            ->color('primary')
                                            ->state(fn (RouteSchedule $record): HtmlString => new HtmlString($record->bus?->seat_config->getTotalSeats() - $record->bus_bookings_sum_passenger_count . ' <span class="text-gray-700 text-sm font-normal">Seats Available</span>'))
                                            ->grow(false),
                                        Action::make('book')
                                            ->label('Book Now')
                                            ->icon(LucideIcon::Ticket)
                                            ->outlined()
                                            ->button()
                                            ->steps([
                                                Step::make('Book your seat')
                                                    ->icon(LucideIcon::Ticket)
                                                    ->schema([
                                                        Tabs::make('decks')
                                                            ->contained(false)
                                                            ->tabs([
                                                                Tab::make('Lower Deck')
                                                                    ->schema([
                                                                        SeatPicker::make('selected_seats_lower')
                                                                            ->hiddenLabel()
                                                                    ]),
                                                                Tab::make('Upper Deck')
                                                                    ->visible(fn (RouteSchedule $record): bool => $record->bus?->seat_config?->hasUpperDeck())
                                                                    ->schema([
                                                                        SeatPicker::make('selected_seats_upper')
                                                                            ->hiddenLabel()
                                                                            ->deck('upper'),
                                                                    ]),
                                                            ]),
                                                    ]),
                                                Step::make('Passenger Details')
                                                    ->icon(LucideIcon::User)
                                                    ->schema([
                                                        TextInput::make('passenger_name')
                                                            ->label('Passenger Name')
                                                            ->required()
                                                            ->columnSpanFull(),
                                                        TextInput::make('passenger_email')
                                                            ->label('Email Address')
                                                            ->email()
                                                            ->required()
                                                            ->columnSpanFull(),
                                                        TextInput::make('passenger_phone')
                                                            ->label('Phone Number')
                                                            ->tel()
                                                            ->required()
                                                            ->columnSpanFull(),
                                                    ]),
                                                Step::make('Payment')
                                                    ->icon(LucideIcon::CreditCard)
                                                    ->schema([
                                                        TextInput::make('card_number')
                                                            ->label('Card Number')
                                                            ->required()
                                                            ->tel()
                                                            ->columnSpanFull(),
                                                        TextInput::make('card_expiry')
                                                            ->label('Card Expiry (MM/YY)')
                                                            ->required()
                                                            ->tel()
                                                            ->columnSpanFull(),
                                                        TextInput::make('card_cvc')
                                                            ->label('Card CVC')
                                                            ->required()
                                                            ->tel()
                                                            ->columnSpanFull(),
                                                    ]),
                                            ]),
                                    ])->grow(false)
                                        ->extraAttributes(['class' => 'items-center']),
                                ])->columnSpanFull()->extraAttributes([
                                    'class' => 'search-result-footer items-center',
                                ]),
                            ]),
                    ]),
            ]);
    }
}
