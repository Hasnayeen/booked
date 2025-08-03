<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusBooking extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'seat_numbers' => 'array',
            'passenger_count' => 'integer',
            'base_fare_per_seat' => 'integer',
            'total_base_fare' => 'integer',
            'taxes' => 'integer',
            'service_charges' => 'integer',
            'boarding_time' => 'datetime:H:i',
            'drop_off_time' => 'datetime:H:i',
            'passenger_details' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the booking that owns this bus booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the route associated with this booking.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
}
