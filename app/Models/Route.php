<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime:H:i',
            'arrival_time' => 'datetime:H:i',
            'distance_km' => 'decimal:2',
            'base_price' => 'integer',
            'is_active' => 'boolean',
            'off_days' => 'array',
            'stops' => 'array',
            'boarding_points' => 'array',
            'drop_off_points' => 'array',
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

                // Handle overnight routes (arrival next day)
                if ($arrival->lt($departure)) {
                    $arrival->addDay();
                }

                return $departure->diff($arrival)->format('%hh %Im');
            },
        );
    }

    /**
     * Get the operator that owns this route.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get the bus assigned to this route.
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }
}
