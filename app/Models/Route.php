<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'operator_id',
        'route_name',
        'origin_city',
        'destination_city',
        'distance_km',
        'estimated_duration',
        'base_price',
        'is_active',
        'stops',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
            'stops' => 'array',
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
}
