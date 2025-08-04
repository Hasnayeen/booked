<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Operator;
use App\Models\Route;
use App\Models\User;

class RoutePolicy
{
    /**
     * Determine whether the user can view any models.
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
     * Determine whether the user can view the model.
     */
    public function view(User $user, Route $route): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        return $user->belongsToOperator($route->operator);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (($operator = filament()->getTenant()) instanceof Operator) {
            return $user->belongsToOperator($operator)
                && $user->hasPermissionInOperator($operator, 'manage_routes');
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Route $route): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (! $user->belongsToOperator($route->operator)) {
            return false;
        }

        return $user->hasPermissionInOperator($route->operator, 'manage_routes');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Route $route): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (! $user->belongsToOperator($route->operator)) {
            return false;
        }

        return $user->hasPermissionInOperator($route->operator, 'manage_routes');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Route $route): bool
    {
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }

        if (! $user->belongsToOperator($route->operator)) {
            return false;
        }

        return $user->hasPermissionInOperator($route->operator, 'manage_routes');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Route $route): bool
    {
        return $user->roles->contains('name', 'Admin');
    }
}
