<?php

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

describe('Operator Management', function (): void {
    beforeEach(function (): void {
        Notification::fake();
    });

    it('can register a new operator', function (): void {
        $user = User::factory()->create();

        $operator = Operator::create([
            'name' => 'Sunshine Hotels',
            'type' => OperatorType::Hotel,
            'status' => OperatorStatus::Pending,
            'contact_email' => 'contact@sunshinehotels.com',
            'contact_phone' => '+1-555-0123',
            'description' => 'Premium hotel chain',
        ]);

        expect($operator)->toBeInstanceOf(Operator::class);
        expect($operator->name)->toBe('Sunshine Hotels');
        expect($operator->type)->toBe(OperatorType::Hotel);
        expect($operator->status)->toBe(OperatorStatus::Pending);
        expect($operator->contact_email)->toBe('contact@sunshinehotels.com');

        $this->assertDatabaseHas('operators', [
            'name' => 'Sunshine Hotels',
            'type' => OperatorType::Hotel->value,
            'status' => OperatorStatus::Pending->value,
        ]);
    });

    it('can approve operator registration', function (): void {
        $admin = User::factory()->create();
        $adminRole = Role::where(['name' => 'Admin'])->first();
        $admin->roles()->attach($adminRole);

        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Pending,
            'contact_email' => 'operator@example.com',
        ]);

        // Simulate admin approval
        $operator->update(['status' => OperatorStatus::Approved]);

        expect($operator->fresh()->status)->toBe(OperatorStatus::Approved);

        $this->assertDatabaseHas('operators', [
            'id' => $operator->id,
            'status' => OperatorStatus::Approved->value,
        ]);
    });

    it('can reject operator registration', function (): void {
        $admin = User::factory()->create();
        $adminRole = Role::where(['name' => 'Admin'])->first();
        $admin->roles()->attach($adminRole);

        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Pending,
            'contact_email' => 'operator@example.com',
        ]);

        // Simulate admin rejection
        $operator->update(['status' => OperatorStatus::Rejected]);

        expect($operator->fresh()->status)->toBe(OperatorStatus::Rejected);

        $this->assertDatabaseHas('operators', [
            'id' => $operator->id,
            'status' => OperatorStatus::Rejected->value,
        ]);
    });

    it('can suspend approved operator', function (): void {
        $admin = User::factory()->create();
        $adminRole = Role::where(['name' => 'Admin'])->first();
        $admin->roles()->attach($adminRole);

        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Approved,
            'contact_email' => 'operator@example.com',
        ]);

        // Simulate admin suspension
        $operator->update(['status' => OperatorStatus::Suspended]);

        expect($operator->fresh()->status)->toBe(OperatorStatus::Suspended);

        $this->assertDatabaseHas('operators', [
            'id' => $operator->id,
            'status' => OperatorStatus::Suspended->value,
        ]);
    });

    it('can filter operators by status', function (): void {
        $pendingOperators = Operator::factory()->count(3)->create([
            'status' => OperatorStatus::Pending,
        ]);

        $approvedOperators = Operator::factory()->count(2)->create([
            'status' => OperatorStatus::Approved,
        ]);

        $pendingFromDb = Operator::where('status', OperatorStatus::Pending)->get();
        $approvedFromDb = Operator::where('status', OperatorStatus::Approved)->get();

        expect($pendingFromDb)->toHaveCount(3);
        expect($approvedFromDb)->toHaveCount(2);

        expect($pendingFromDb->pluck('id')->toArray())
            ->toEqual($pendingOperators->pluck('id')->toArray());
        expect($approvedFromDb->pluck('id')->toArray())
            ->toEqual($approvedOperators->pluck('id')->toArray());
    });

    it('can filter operators by type', function (): void {
        $hotelOperators = Operator::factory()->count(2)->create([
            'type' => OperatorType::Hotel,
        ]);

        $busOperators = Operator::factory()->count(3)->create([
            'type' => OperatorType::Bus,
        ]);

        $hotelsFromDb = Operator::where('type', OperatorType::Hotel)->get();
        $busesFromDb = Operator::where('type', OperatorType::Bus)->get();

        expect($hotelsFromDb)->toHaveCount(2);
        expect($busesFromDb)->toHaveCount(3);

        // Check that all hotel operators are in the filtered result
        foreach ($hotelOperators as $hotelOperator) {
            expect($hotelsFromDb->contains('id', $hotelOperator->id))->toBeTrue();
        }

        // Check that all bus operators are in the filtered result
        foreach ($busOperators as $busOperator) {
            expect($busesFromDb->contains('id', $busOperator->id))->toBeTrue();
        }
    });
});
