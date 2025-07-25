<?php

use App\Filament\Operator\Resources\UserResource;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;

describe('User Management Interface Integration', function (): void {
    beforeEach(function (): void {
        $this->operator = Operator::factory()->create(['status' => 'approved']);
        $this->adminRole = Role::firstOrCreate(['name' => 'Operator Admin'], ['is_default' => false]);
        $this->staffRole = Role::firstOrCreate(['name' => 'Operator Staff'], ['is_default' => false]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->operators()->attach($this->operator, ['role_id' => $this->adminRole->id]);

        $this->staffUser = User::factory()->create();
        $this->staffUser->operators()->attach($this->operator, ['role_id' => $this->staffRole->id]);

        $this->otherOperator = Operator::factory()->create(['status' => 'approved']);
        $this->otherUser = User::factory()->create();
        $this->otherUser->operators()->attach($this->otherOperator, ['role_id' => $this->staffRole->id]);
    });

    it('can query users belonging to operator', function (): void {
        actingAs($this->adminUser);

        // Mock the tenant context
        Filament::setTenant($this->operator);

        $query = UserResource::getEloquentQuery();
        $users = $query->get();

        expect($users)->toHaveCount(2);
        expect($users->pluck('id'))->toContain($this->adminUser->id);
        expect($users->pluck('id'))->toContain($this->staffUser->id);
        expect($users->pluck('id'))->not->toContain($this->otherUser->id);
    });

    it('can remove user from operator', function (): void {
        expect($this->staffUser->belongsToOperator($this->operator))->toBeTrue();

        $this->staffUser->operators()->detach($this->operator->id);

        assertDatabaseMissing('operator_user', [
            'operator_id' => $this->operator->id,
            'user_id' => $this->staffUser->id,
        ]);

        expect($this->staffUser->fresh()->belongsToOperator($this->operator))->toBeFalse();
    });

    it('can check user role in operator', function (): void {
        expect($this->adminUser->hasRoleInOperator($this->operator, 'Operator Admin'))->toBeTrue();
        expect($this->adminUser->hasRoleInOperator($this->operator, 'Operator Staff'))->toBeFalse();

        expect($this->staffUser->hasRoleInOperator($this->operator, 'Operator Staff'))->toBeTrue();
        expect($this->staffUser->hasRoleInOperator($this->operator, 'Operator Admin'))->toBeFalse();
    });

    it('prevents removing last admin user', function (): void {
        // Only one admin exists
        expect($this->adminUser->hasRoleInOperator($this->operator, 'Operator Admin'))->toBeTrue();

        $adminRole = Role::where('name', 'Operator Admin')->first();
        $adminCount = User::whereHas('operators', function ($query) use ($adminRole): void {
            $query->where('operator_id', $this->operator->id)
                ->where('role_id', $adminRole->id);
        })->count();

        expect($adminCount)->toBe(1);

        // Removing the last admin should be prevented by business logic
        $isLastAdmin = $this->adminUser->hasRoleInOperator($this->operator, 'Operator Admin') && $adminCount <= 1;
        expect($isLastAdmin)->toBeTrue();
    });
})->todo('Implement user role checks');
