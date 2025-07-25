<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bus extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'operator_id',
        'bus_number',
        'bus_type',
        'total_seats',
        'license_plate',
        'is_active',
        'amenities',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
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
}
