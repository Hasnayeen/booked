<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\SeatConfigurationCast;
use App\Enums\BusCategory;
use App\Enums\BusType;
use App\ValueObjects\SeatConfiguration;
use App\ValueObjects\SeatDeck;
use App\ValueObjects\SeatPosition;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'category' => BusCategory::class,
            'type' => BusType::class,
            'total_seats' => 'integer',
            'is_active' => 'boolean',
            'amenities' => 'array',
            'metadata' => 'array',
            'seat_config' => SeatConfigurationCast::class,
        ];
    }

    /**
     * Get the operator that owns this bus.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get the bookings for this bus.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function busBookings(): HasManyThrough
    {
        return $this->hasManyThrough(
            BusBooking::class,
            RouteSchedule::class,
            'bus_id',     // Foreign key on RouteSchedule
            'route_schedule_id', // Foreign key on BusBooking
            'id',         // Local key on Bus
            'id',          // Local key on RouteSchedule
        );
    }

    /**
     * Scope to filter active buses.
     */
    #[Scope]
    protected function active($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter buses by category.
     */
    #[Scope]
    protected function byCategory($query, BusCategory $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter buses by bus type (AC/Non AC).
     */
    #[Scope]
    protected function byBusType($query, BusType $busType)
    {
        return $query->where('type', $busType);
    }

    /**
     * Get the display name for the bus.
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(get: function (): string {
            $category = $this->category?->getLabel() ?? 'Standard';
            $busType = $this->type?->getLabel() ?? '';

            return trim("{$this->bus_number} ({$category} {$busType})");
        });
    }

    /**
     * Check if the bus has a specific amenity.
     */
    public function hasAmenity(string $amenity): bool
    {
        return in_array($amenity, $this->amenities ?? []);
    }

    /**
     * Get available seats (total seats minus booked seats).
     */
    protected function availableSeats(): Attribute
    {
        return Attribute::make(get: function (): int|float {
            $bookedSeats = $this->bookings()->where('status', 'confirmed')->sum('number_of_seats');

            return $this->total_seats - $bookedSeats;
        });
    }

    /**
     * Get the minimum price from the seat configuration.
     */
    protected function minPrice(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->seat_config?->getBasePriceInCents() ?? 0,
        );
    }

    /**
     * Get the maximum price from the seat configuration.
     */
    protected function maxPrice(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->seat_config?->getMaxPriceInCents() ?? 0,
        );
    }

    /**
     * Get all prices from the seat configuration.
     */
    protected function allPrices(): Attribute
    {
        return Attribute::make(
            get: fn (): array => $this->seat_config?->getAllPricesInCents() ?? [],
        );
    }

    public function getSeatConfigurationForDate(string $travelDate, int $routeScheduleId): SeatConfiguration
    {
        $baseSeatConfig = $this->seat_config;

        $bookedSeatPositions = $this->getBookedSeatsForDate($travelDate, $routeScheduleId);

        return $this->buildSeatConfigurationWithAvailability($baseSeatConfig, $bookedSeatPositions);
    }

    private function getBookedSeatsForDate(string $travelDate, int $routeScheduleId): \Illuminate\Support\Collection
    {
        return $this->busBookings()
            ->where('travel_date', $travelDate)
            ->where('route_schedule_id', $routeScheduleId)
            ->get()
            ->flatMap(fn ($booking) =>
                // Assuming seat_numbers contains SeatPosition objects
                collect($booking->seat_numbers));
    }

    private function buildSeatConfigurationWithAvailability(
        SeatConfiguration $baseSeatConfig,
        \Illuminate\Support\Collection $bookedSeatPositions,
    ): SeatConfiguration {
        // Clone the base configuration
        $lowerDeck = $this->updateDeckAvailability($baseSeatConfig->lowerDeck, $bookedSeatPositions);
        $upperDeck = $baseSeatConfig->upperDeck instanceof SeatDeck
            ? $this->updateDeckAvailability($baseSeatConfig->upperDeck, $bookedSeatPositions)
            : null;

        return new SeatConfiguration(
            deckType: $baseSeatConfig->deckType,
            lowerDeck: $lowerDeck,
            upperDeck: $upperDeck,
        );
    }

    private function updateDeckAvailability(SeatDeck $deck, \Illuminate\Support\Collection $bookedSeatPositions): SeatDeck
    {
        $seats = $deck->getSeats()->map(function (SeatPosition $seat) use ($bookedSeatPositions): SeatPosition {
            $isBooked = $bookedSeatPositions->contains(
                fn (SeatPosition $bookedSeat): bool => $bookedSeat->seatNumber === $seat->seatNumber,
            );

            return new SeatPosition(
                seatNumber: $seat->seatNumber,
                row: $seat->row,
                column: $seat->column,
                rowLabel: $seat->rowLabel,
                columnLabel: $seat->columnLabel,
                isAvailable: ! $isBooked,
                priceInCents: $seat->priceInCents,
            );
        });

        return new SeatDeck(
            seatType: $deck->seatType,
            totalColumns: $deck->totalColumns,
            columnLabel: $deck->columnLabel,
            columnLayout: $deck->columnLayout,
            totalRows: $deck->totalRows,
            rowLabel: $deck->rowLabel,
            pricePerSeatInCents: $deck->pricePerSeatInCents,
            seats: $seats,
            rowOffset: $deck->rowOffset,
            columnOffset: $deck->columnOffset,
        );
    }
}
