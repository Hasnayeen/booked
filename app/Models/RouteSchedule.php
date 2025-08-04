<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['estimated_duration'];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime:H:i',
            'arrival_time' => 'datetime:H:i',
            'is_active' => 'boolean',
            'off_days' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the estimated duration calculated from departure and arrival times.
     */
    protected function estimatedDuration(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->departure_time || ! $this->arrival_time) {
                    return null;
                }

                $departure = Carbon::parse($this->departure_time);
                $arrival = Carbon::parse($this->arrival_time);

                // Handle overnight schedules (arrival next day)
                if ($arrival->lt($departure)) {
                    $arrival->addDay();
                }

                return $departure->diff($arrival)->format('%hh %Im');
            },
        );
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get the route that owns this schedule.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the bus assigned to this schedule.
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the bus bookings for this schedule.
     */
    public function busBookings(): HasMany
    {
        return $this->hasMany(BusBooking::class);
    }
}
