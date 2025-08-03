<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelBooking extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'booking_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'nights',
        'guests',
        'adults',
        'children',
        'room_rate_per_night',
        'total_room_amount',
        'taxes',
        'service_charges',
        'special_requests',
        'guest_details',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights' => 'integer',
            'guests' => 'integer',
            'adults' => 'integer',
            'children' => 'integer',
            'room_rate_per_night' => 'integer',
            'total_room_amount' => 'integer',
            'taxes' => 'integer',
            'service_charges' => 'integer',
            'guest_details' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the booking that owns this hotel booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the room associated with this booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
