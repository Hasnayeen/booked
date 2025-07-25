<?php

namespace App\Policies;

use App\Models\Operator;
use App\Models\User;

class OperatorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all operators
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Operator $operator): bool
    {
        // Admin can view any operator, or user can view operators they belong to
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can register a new operator
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Operator $operator): bool
    {
        if ($user->belongsToOperator($operator)) {
            return true;
        }
        return (bool) $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Operator $operator): bool
    {
        // Only admins can delete operators
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Operator $operator): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Operator $operator): bool
    {
        return false;
    }
}
