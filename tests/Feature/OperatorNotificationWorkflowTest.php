<?php

use App\Enums\OperatorStatus;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use App\Notifications\OperatorStatusUpdate;
use Illuminate\Support\Facades\Notification;

describe('Operator Notifications', function (): void {
    beforeEach(function (): void {
        Notification::fake();
    });

    it('sends notification when operator status is updated', function (): void {
        $admin = User::factory()->create();
        $adminRole = Role::where(['name' => 'Admin'])->first();
        $admin->roles()->attach($adminRole);

        $operator = Operator::factory()->create([
            'status' => OperatorStatus::PENDING,
            'contact_email' => 'operator@example.com',
        ]);

        // Update the operator status
        $operator->update(['status' => OperatorStatus::APPROVED]);

        // Create notification instance and test its properties
        $notification = new OperatorStatusUpdate($operator, OperatorStatus::PENDING);

        expect($notification->operator)->toBe($operator);
        expect($notification->oldStatus)->toBe(OperatorStatus::PENDING);
        expect($notification->operator->status)->toBe(OperatorStatus::APPROVED);

        // Test notification data
        $arrayData = $notification->toArray($admin);
        expect($arrayData)->toHaveKey('operator_id');
        expect($arrayData)->toHaveKey('operator_name');
        expect($arrayData)->toHaveKey('old_status');
        expect($arrayData)->toHaveKey('new_status');
        expect($arrayData['operator_id'])->toBe($operator->id);
        expect($arrayData['new_status'])->toBe(OperatorStatus::APPROVED->value);
        expect($arrayData['old_status'])->toBe(OperatorStatus::PENDING->value);
    });

    it('sends different notification types for different status changes', function (): void {
        $operator = Operator::factory()->create([
            'contact_email' => 'operator@example.com',
        ]);

        // Test approval notification (operator status is now approved)
        $operator->update(['status' => OperatorStatus::APPROVED]);
        $approvalNotification = new OperatorStatusUpdate($operator, OperatorStatus::PENDING);
        expect($approvalNotification->operator->status)->toBe(OperatorStatus::APPROVED);
        expect($approvalNotification->oldStatus)->toBe(OperatorStatus::PENDING);

        // Test rejection notification
        $operator->update(['status' => OperatorStatus::REJECTED]);
        $rejectionNotification = new OperatorStatusUpdate($operator, OperatorStatus::PENDING);
        expect($rejectionNotification->operator->status)->toBe(OperatorStatus::REJECTED);
        expect($rejectionNotification->oldStatus)->toBe(OperatorStatus::PENDING);

        // Test suspension notification
        $operator->update(['status' => OperatorStatus::SUSPENDED]);
        $suspensionNotification = new OperatorStatusUpdate($operator, OperatorStatus::APPROVED);
        expect($suspensionNotification->operator->status)->toBe(OperatorStatus::SUSPENDED);
        expect($suspensionNotification->oldStatus)->toBe(OperatorStatus::APPROVED);
    });
});
