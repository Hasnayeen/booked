<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'total_amount' => 'decimal:2',
            'booking_date' => 'date',
            'booking_details' => 'array',
            'metadata' => 'array',
        ];
    }

    public function uniqueIds(): array
    {
        return ['booking_reference'];
    }

    /**
     * Get the operator that owns the booking.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get the user that made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the hotel booking details (if this is a hotel booking).
     */
    public function hotelBooking(): HasOne
    {
        return $this->hasOne(HotelBooking::class);
    }

    /**
     * Get the bus booking details (if this is a bus booking).
     */
    public function busBooking(): HasOne
    {
        return $this->hasOne(BusBooking::class);
    }
}
