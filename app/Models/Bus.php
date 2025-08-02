<?php

namespace App\Models;

use App\Casts\SeatConfigurationCast;
use App\Enums\BusCategory;
use App\Enums\BusType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use HasFactory, SoftDeletes;

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
}
