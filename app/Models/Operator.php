<?php

namespace App\Models;

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model implements HasName
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => OperatorType::class,
            'status' => OperatorStatus::class,
            'metadata' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(OperatorUser::class)
            ->withPivot(['role_id', 'joined_at'])
            ->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get all buses for this operator (bus operators only).
     */
    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    public function bookingsOfType(string $type): HasMany
    {
        return $this->hasMany(Booking::class)->where('booking_type', $type);
    }

    public function hotelBookings(): HasMany
    {
        return $this->bookingsOfType('hotel');
    }

    public function busBookings(): HasMany
    {
        return $this->bookingsOfType('bus');
    }

    public function usersWithRole(string $roleName): BelongsToMany
    {
        return $this->users()
            ->whereExists(function ($query) use ($roleName): void {
                $query->select('*')
                    ->from('roles')
                    ->whereColumn('roles.id', 'operator_user.role_id')
                    ->where('roles.name', $roleName);
            });
    }

    public function adminUsers(): BelongsToMany
    {
        return $this->usersWithRole('Operator Admin');
    }

    public function memberUsers(): BelongsToMany
    {
        return $this->usersWithRole('Operator Member');
    }

    public function getFilamentName(): string
    {
        return "{$this->name} ({$this->type->label()})";
    }
}
