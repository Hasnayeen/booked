<?php

declare(strict_types=1);

use App\Filament\Forms\Components\SeatLayout;
use App\Models\Bus;
use App\Models\Operator;
use App\Models\Route;
use App\Models\RouteSchedule;
use App\Models\User;
use App\ValueObjects\SeatConfiguration;

beforeEach(function (): void {
    $this->adminUser = User::factory()->create();
    $this->operator = Operator::factory()->create();
    $this->bus = Bus::factory()->for($this->operator)->create();
    $this->route = Route::factory()->for($this->operator)->create();
    $this->routeSchedule = RouteSchedule::factory()
        ->for($this->route)
        ->for($this->bus)
        ->create();
});

describe('Seat Layout Component', function (): void {
    it('can create seat layout component instance', function (): void {
        $seatLayout = SeatLayout::make('selected_seats');

        expect($seatLayout)->toBeInstanceOf(SeatLayout::class);
        expect($seatLayout->getName())->toBe('selected_seats');
    });

    it('can set route schedule and travel date', function (): void {
        $seatLayout = SeatLayout::make('selected_seats')
            ->routeSchedule($this->routeSchedule)
            ->travelDate('2024-12-25')
            ->passengerCount(2);

        expect($seatLayout->getRouteSchedule())->toBe($this->routeSchedule);
        expect($seatLayout->getTravelDate())->toBe('2024-12-25');
        expect($seatLayout->getPassengerCount())->toBe(2);
    });

    it('can get seat configuration from route schedule', function (): void {
        $seatLayout = SeatLayout::make('selected_seats')
            ->routeSchedule($this->routeSchedule)
            ->travelDate('2024-12-25');

        $seatConfiguration = $seatLayout->getSeatConfiguration();

        expect($seatConfiguration)->toBeInstanceOf(SeatConfiguration::class);
    });

    it('returns null when route schedule or travel date is missing', function (): void {
        $seatLayout = SeatLayout::make('selected_seats');

        expect($seatLayout->getSeatConfiguration())->toBeNull();

        $seatLayout->routeSchedule($this->routeSchedule);
        expect($seatLayout->getSeatConfiguration())->toBeNull();

        $seatLayout = SeatLayout::make('selected_seats')->travelDate('2024-12-25');
        expect($seatLayout->getSeatConfiguration())->toBeNull();
    });

    it('can configure multiple selection settings', function (): void {
        $seatLayout = SeatLayout::make('selected_seats')
            ->allowMultipleSelection(false);

        expect($seatLayout->getAllowMultipleSelection())->toBeFalse();

        $seatLayout->allowMultipleSelection(true);
        expect($seatLayout->getAllowMultipleSelection())->toBeTrue();
    });

    it('supports closure-based configuration', function (): void {
        $seatLayout = SeatLayout::make('selected_seats')
            ->routeSchedule(fn () => $this->routeSchedule)
            ->travelDate(fn (): string => '2024-12-25')
            ->passengerCount(fn (): int => 3)
            ->allowMultipleSelection(fn (): false => false);

        expect($seatLayout->getRouteSchedule())->toBe($this->routeSchedule);
        expect($seatLayout->getTravelDate())->toBe('2024-12-25');
        expect($seatLayout->getPassengerCount())->toBe(3);
        expect($seatLayout->getAllowMultipleSelection())->toBeFalse();
    });
});
