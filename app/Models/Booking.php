<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasUuids;

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
}
