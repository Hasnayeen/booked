<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
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
            'estimated_duration' => 'datetime:H:i',
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
