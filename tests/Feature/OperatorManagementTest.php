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
            'type' => OperatorType::HOTEL,
            'status' => OperatorStatus::PENDING,
            'contact_email' => 'contact@sunshinehotels.com',
            'contact_phone' => '+1-555-0123',
            'description' => 'Premium hotel chain',
        ]);

        expect($operator)->toBeInstanceOf(Operator::class);
        expect($operator->name)->toBe('Sunshine Hotels');
        expect($operator->type)->toBe(OperatorType::HOTEL);
        expect($operator->status)->toBe(OperatorStatus::PENDING);
        expect($operator->contact_email)->toBe('contact@sunshinehotels.com');

        $this->assertDatabaseHas('operators', [
            'name' => 'Sunshine Hotels',
            'type' => OperatorType::HOTEL->value,
            'status' => OperatorStatus::PENDING->value,
        ]);
    });

    it('can approve operator registration', function (): void {
        $admin = User::factory()->create();
        $adminRole = Role::where(['name' => 'Admin'])->first();
        $admin->roles()->attach($adminRole);

        $operator = Operator::factory()->create([
            'status' => OperatorStatus::PENDING,
            'contact_email' => 'operator@example.com',
        ]);

        // Simulate admin approval
        $operator->update(['status' => OperatorStatus::APPROVED]);

        expect($operator->fresh()->status)->toBe(OperatorStatus::APPROVED);

        $this->assertDatabaseHas('operators', [
            'id' => $operator->id,
            'status' => OperatorStatus::APPROVED->value,
        ]);
    });

    it('can reject operator registration', function (): void {
        $admin = User::factory()->create();
        $adminRole = Role::where(['name' => 'Admin'])->first();
        $admin->roles()->attach($adminRole);

        $operator = Operator::factory()->create([
            'status' => OperatorStatus::PENDING,
            'contact_email' => 'operator@example.com',
        ]);

        // Simulate admin rejection
        $operator->update(['status' => OperatorStatus::REJECTED]);

        expect($operator->fresh()->status)->toBe(OperatorStatus::REJECTED);

        $this->assertDatabaseHas('operators', [
            'id' => $operator->id,
            'status' => OperatorStatus::REJECTED->value,
        ]);
    });

    it('can suspend approved operator', function (): void {
        $admin = User::factory()->create();
        $adminRole = Role::where(['name' => 'Admin'])->first();
        $admin->roles()->attach($adminRole);

        $operator = Operator::factory()->create([
            'status' => OperatorStatus::APPROVED,
            'contact_email' => 'operator@example.com',
        ]);

        // Simulate admin suspension
        $operator->update(['status' => OperatorStatus::SUSPENDED]);

        expect($operator->fresh()->status)->toBe(OperatorStatus::SUSPENDED);

        $this->assertDatabaseHas('operators', [
            'id' => $operator->id,
            'status' => OperatorStatus::SUSPENDED->value,
        ]);
    });

    it('can filter operators by status', function (): void {
        $pendingOperators = Operator::factory()->count(3)->create([
            'status' => OperatorStatus::PENDING,
        ]);

        $approvedOperators = Operator::factory()->count(2)->create([
            'status' => OperatorStatus::APPROVED,
        ]);

        $pendingFromDb = Operator::where('status', OperatorStatus::PENDING)->get();
        $approvedFromDb = Operator::where('status', OperatorStatus::APPROVED)->get();

        expect($pendingFromDb)->toHaveCount(3);
        expect($approvedFromDb)->toHaveCount(2);

        expect($pendingFromDb->pluck('id')->toArray())
            ->toEqual($pendingOperators->pluck('id')->toArray());
        expect($approvedFromDb->pluck('id')->toArray())
            ->toEqual($approvedOperators->pluck('id')->toArray());
    });

    it('can filter operators by type', function (): void {
        $hotelOperators = Operator::factory()->count(2)->create([
            'type' => OperatorType::HOTEL,
        ]);

        $busOperators = Operator::factory()->count(3)->create([
            'type' => OperatorType::BUS,
        ]);

        $hotelsFromDb = Operator::where('type', OperatorType::HOTEL)->get();
        $busesFromDb = Operator::where('type', OperatorType::BUS)->get();

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
