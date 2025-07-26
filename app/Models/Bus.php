<?php

namespace App\Models;

use App\Enums\BusCategory;
use App\Enums\BusType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;

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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter buses by category.
     */
    public function scopeByCategory($query, BusCategory $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter buses by bus type (AC/Non AC).
     */
    public function scopeByBusType($query, BusType $busType)
    {
        return $query->where('type', $busType);
    }

    /**
     * Get the display name for the bus.
     */
    public function getDisplayNameAttribute(): string
    {
        $category = $this->category?->getLabel() ?? 'Standard';
        $busType = $this->type?->getLabel() ?? '';

        return trim("{$this->bus_number} ({$category} {$busType})");
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
    public function getAvailableSeatsAttribute(): int
    {
        $bookedSeats = $this->bookings()->where('status', 'confirmed')->sum('number_of_seats');

        return $this->total_seats - $bookedSeats;
    }
}
