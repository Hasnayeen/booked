<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            'distance_km' => 'decimal:2',
            'is_active' => 'boolean',
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
     * Get the schedules for this route.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(RouteSchedule::class);
    }

    /**
     * Get the active schedules for this route.
     */
    public function activeSchedules(): HasMany
    {
        return $this->schedules()->where('is_active', true);
    }
}
