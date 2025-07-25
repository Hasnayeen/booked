<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'operator_id',
        'room_number',
        'room_type',
        'price_per_night',
        'capacity',
        'description',
        'amenities',
        'is_available',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'capacity' => 'integer',
            'is_available' => 'boolean',
            'amenities' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the operator that owns this room.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
