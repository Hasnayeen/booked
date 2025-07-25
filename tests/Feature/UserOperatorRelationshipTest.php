<?php

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User-Operator Workflow Management', function (): void {
    beforeEach(function (): void {
        // Set the current Filament panel for testing
        Filament::setCurrentPanel(Filament::getPanel('operator'));

        // Create roles first
        $this->operatorAdminRole = Role::where(['name' => 'Operator Admin'])->first();
        $this->operatorMemberRole = Role::where(['name' => 'Operator Member'])->first();
        $this->operatorStaffRole = Role::where(['name' => 'Operator Staff'])->first();
        $this->operatorViewerRole = Role::where(['name' => 'Operator Viewer'])->first();

        $this->user = User::factory()->create([
            'email' => 'user@booked.com',
            'email_verified_at' => now(),
        ]);

        $this->hotelOperator = Operator::factory()->create([
            'name' => 'Grand Hotel',
            'type' => OperatorType::HOTEL,
            'status' => OperatorStatus::APPROVED,
        ]);

        $this->busOperator = Operator::factory()->create([
            'name' => 'Express Bus Lines',
            'type' => OperatorType::BUS,
            'status' => OperatorStatus::APPROVED,
        ]);

        $this->restrictedOperator = Operator::factory()->create([
            'name' => 'VIP Resort',
            'type' => OperatorType::HOTEL,
            'status' => OperatorStatus::APPROVED,
        ]);
    });

    describe('User Access Management', function (): void {
        it('user can be added to an operator as a member', function (): void {
            // Add user to hotel operator
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            expect($this->user->belongsToOperator($this->hotelOperator))->toBeTrue();
            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Member'))->toBeTrue();
            expect($this->user->canAccessTenant($this->hotelOperator))->toBeTrue();
        });

        it('user can be added to an operator as an admin', function (): void {
            // Add user to bus operator as admin
            $this->busOperator->users()->attach($this->user, [
                'role_id' => $this->operatorAdminRole->id,
                'joined_at' => now(),
            ]);

            expect($this->user->belongsToOperator($this->busOperator))->toBeTrue();
            expect($this->user->hasRoleInOperator($this->busOperator, 'Operator Admin'))->toBeTrue();
            expect($this->user->canAccessTenant($this->busOperator))->toBeTrue();
        });

        it('user member of an operator has access to that operator', function (): void {
            // Add user to hotel operator
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            $this->actingAs($this->user);

            // User can access the hotel operator
            expect($this->user->canAccessTenant($this->hotelOperator))->toBeTrue();

            // User can see the operator in their tenant list
            $panel = Filament::getPanel('operator');
            $availableTenants = $this->user->getTenants($panel);
            expect($availableTenants->contains($this->hotelOperator))->toBeTrue();
        });

        it('user not member of an operator cannot access that operator', function (): void {
            $this->actingAs($this->user);

            // User cannot access the restricted operator
            expect($this->user->canAccessTenant($this->restrictedOperator))->toBeFalse();

            // User cannot see the operator in their tenant list
            $panel = Filament::getPanel('operator');
            $availableTenants = $this->user->getTenants($panel);
            expect($availableTenants->contains($this->restrictedOperator))->toBeFalse();
        });

        it('user can be removed from an operator and loses access', function (): void {
            // Initially add user to operator
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            expect($this->user->canAccessTenant($this->hotelOperator))->toBeTrue();

            // Remove user from operator
            $this->hotelOperator->users()->detach($this->user);

            // User loses access
            $this->user->refresh();
            expect($this->user->canAccessTenant($this->hotelOperator))->toBeFalse();
        });
    });

    describe('Multi-Operator Workflows', function (): void {
        it('user can be member of multiple operators', function (): void {
            // Add user to both operators
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorAdminRole->id,
                'joined_at' => now(),
            ]);

            $this->busOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            // User has access to both
            expect($this->user->canAccessTenant($this->hotelOperator))->toBeTrue();
            expect($this->user->canAccessTenant($this->busOperator))->toBeTrue();

            // User can see both operators in tenant list
            $panel = Filament::getPanel('operator');
            $availableTenants = $this->user->getTenants($panel);
            expect($availableTenants)->toHaveCount(2);
            expect($availableTenants->contains($this->hotelOperator))->toBeTrue();
            expect($availableTenants->contains($this->busOperator))->toBeTrue();
        });

        it('user can switch between different operators in panel', function (): void {
            // Add user to both operators
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorAdminRole->id,
                'joined_at' => now(),
            ]);

            $this->busOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            $this->actingAs($this->user);

            // User can switch to hotel operator
            Filament::setTenant($this->hotelOperator);
            expect(Filament::getTenant())->toBe($this->hotelOperator);

            // User can switch to bus operator
            Filament::setTenant($this->busOperator);
            expect(Filament::getTenant())->toBe($this->busOperator);
        });

        it('user has different roles in different operators', function (): void {
            // Add user with different roles in each operator
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorAdminRole->id,
                'joined_at' => now(),
            ]);

            $this->busOperator->users()->attach($this->user, [
                'role_id' => $this->operatorStaffRole->id,
                'joined_at' => now(),
            ]);

            // Verify different roles
            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Admin'))->toBeTrue();
            expect($this->user->hasRoleInOperator($this->busOperator, 'Operator Staff'))->toBeTrue();
            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Staff'))->toBeFalse();
            expect($this->user->hasRoleInOperator($this->busOperator, 'Operator Admin'))->toBeFalse();
        });
    });

    describe('Role-Based Access Workflows', function (): void {
        it('admin user can perform administrative tasks in operator', function (): void {
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorAdminRole->id,
                'joined_at' => now(),
            ]);

            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Admin'))->toBeTrue();

            // Verify admin has expected access
            $this->actingAs($this->user);
            Filament::setTenant($this->hotelOperator);
            expect(Filament::getTenant())->toBe($this->hotelOperator);
        });

        it('member user has limited access in operator', function (): void {
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Member'))->toBeTrue();
            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Admin'))->toBeFalse();

            // Member still has basic access to operator
            $this->actingAs($this->user);
            expect($this->user->canAccessTenant($this->hotelOperator))->toBeTrue();
        });

        it('user role can be upgraded in operator', function (): void {
            // Start as member
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Member'))->toBeTrue();

            // Upgrade to admin
            $this->hotelOperator->users()->updateExistingPivot($this->user, [
                'role_id' => $this->operatorAdminRole->id,
            ]);

            // Refresh user to get updated pivot data
            $this->user->refresh();
            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Admin'))->toBeTrue();
            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Member'))->toBeFalse();
        });
    });

    describe('Panel Authentication Workflows', function (): void {
        it('authenticated user can access operator panel when member of operators', function (): void {
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorMemberRole->id,
                'joined_at' => now(),
            ]);

            $panel = Filament::getPanel('operator');
            expect($this->user->canAccessPanel($panel))->toBeTrue();
        });

        it('user without operators can still access panel for registration', function (): void {
            // User not member of any operators
            $panel = Filament::getPanel('operator');
            expect($this->user->canAccessPanel($panel))->toBeTrue(); // Should be true for registration
        });
    });

    describe('Business Workflow Scenarios', function (): void {
        it('hotel manager can invite staff to their operator', function (): void {
            // Hotel manager is added as admin
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorAdminRole->id,
                'joined_at' => now(),
            ]);

            // Create a staff member
            $staffUser = User::factory()->create([
                'email' => 'staff@hotel.com',
            ]);

            // Manager can add staff to their operator
            $this->hotelOperator->users()->attach($staffUser, [
                'role_id' => $this->operatorStaffRole->id,
                'joined_at' => now(),
            ]);

            expect($staffUser->belongsToOperator($this->hotelOperator))->toBeTrue();
            expect($staffUser->hasRoleInOperator($this->hotelOperator, 'Operator Staff'))->toBeTrue();
            expect($staffUser->canAccessTenant($this->hotelOperator))->toBeTrue();
        });

        it('user can work for competing operators in different roles', function (): void {
            $competingHotel = Operator::factory()->create([
                'name' => 'Luxury Resort',
                'type' => OperatorType::HOTEL,
                'status' => OperatorStatus::APPROVED,
            ]);

            // User works as admin for one hotel
            $this->hotelOperator->users()->attach($this->user, [
                'role_id' => $this->operatorAdminRole->id,
                'joined_at' => now(),
            ]);

            // And as staff for competing hotel
            $competingHotel->users()->attach($this->user, [
                'role_id' => $this->operatorStaffRole->id,
                'joined_at' => now(),
            ]);

            expect($this->user->belongsToOperator($this->hotelOperator))->toBeTrue();
            expect($this->user->belongsToOperator($competingHotel))->toBeTrue();
            expect($this->user->hasRoleInOperator($this->hotelOperator, 'Operator Admin'))->toBeTrue();
            expect($this->user->hasRoleInOperator($competingHotel, 'Operator Staff'))->toBeTrue();
        });
    });
});
