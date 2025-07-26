<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->roles->contains('name', 'Admin'),
            default => true,
        };
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class)
            ->using(OperatorUser::class)
            ->withPivot(['role_id', 'joined_at'])
            ->withTimestamps();
    }

    public function operatorsWithRole(string $roleName): BelongsToMany
    {
        return $this->operators()
            ->whereExists(function ($query) use ($roleName): void {
                $query->select('*')
                    ->from('roles')
                    ->whereColumn('roles.id', 'operator_user.role_id')
                    ->where('roles.name', $roleName);
            });
    }

    public function operatorsAsAdmin(): BelongsToMany
    {
        return $this->operatorsWithRole('Operator Admin');
    }

    public function belongsToOperator(int|Operator $operator): bool
    {
        $operatorId = $operator instanceof Operator ? $operator->id : $operator;

        return $this->operators()->where('operator_id', $operatorId)->exists();
    }

    public function hasRoleInOperator(int|Operator $operator, string $roleName): bool
    {
        $operatorId = $operator instanceof Operator ? $operator->id : $operator;

        return $this->operators()
            ->where('operator_id', $operatorId)
            ->whereExists(function ($query) use ($roleName): void {
                $query->select('*')
                    ->from('roles')
                    ->whereColumn('roles.id', 'operator_user.role_id')
                    ->where('roles.name', $roleName);
            })
            ->exists();
    }

    public function getRoleInOperator(int|Operator $operator): ?Role
    {
        $operatorId = $operator instanceof Operator ? $operator->id : $operator;

        return OperatorUser::where('user_id', $this->id)
            ->where('operator_id', $operatorId)
            ->with('role')
            ->first()?->role;
    }

    public function hasPermissionInOperator(int|Operator $operator, string $permissionName): bool
    {
        return $this->getRoleInOperator($operator)?->permissions->pluck('name')->contains($permissionName);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->operators;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->belongsToOperator($tenant);
    }
}
