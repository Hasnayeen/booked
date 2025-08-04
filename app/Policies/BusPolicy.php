<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bus;
use App\Models\Operator;
use App\Models\User;

class BusPolicy
{
    /**
     * Determine whether the user can view any buses.
     */
    public function viewAny(User $user): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (($operator = filament()->getTenant()) instanceof Operator) {
            return $user->belongsToOperator($operator);
        }

        return false;
    }

    /**
     * Determine whether the user can view the bus.
     */
    public function view(User $user, Bus $bus): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        return $user->belongsToOperator($bus->operator);
    }

    /**
     * Determine whether the user can create buses.
     */
    public function create(User $user): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (($operator = filament()->getTenant()) instanceof Operator) {
            return $user->belongsToOperator($operator)
                && $user->hasPermissionInOperator($operator, 'manage_buses');
        }

        return false;
    }

    /**
     * Determine whether the user can update the bus.
     */
    public function update(User $user, Bus $bus): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (! $user->belongsToOperator($bus->operator)) {
            return false;
        }

        return $user->hasPermissionInOperator($bus->operator, 'manage_buses');
    }

    /**
     * Determine whether the user can delete the bus.
     */
    public function delete(User $user, Bus $bus): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (! $user->belongsToOperator($bus->operator)) {
            return false;
        }

        return $user->hasPermissionInOperator($bus->operator, 'manage_buses');
    }

    public function deleteAny(User $user): bool
    {
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can restore the bus.
     */
    public function restore(User $user, Bus $bus): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (! $user->belongsToOperator($bus->operator)) {
            return false;
        }

        return $user->hasPermissionInOperator($bus->operator, 'manage_buses');
    }

    /**
     * Determine whether the user can permanently delete the bus.
     */
    public function forceDelete(User $user, Bus $bus): bool
    {
        return $user->roles->contains('name', 'Admin');
    }
}
