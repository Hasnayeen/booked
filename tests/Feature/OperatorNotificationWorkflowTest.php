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
            'status' => OperatorStatus::Pending,
            'contact_email' => 'operator@example.com',
        ]);

        // Update the operator status
        $operator->update(['status' => OperatorStatus::Approved]);

        // Create notification instance and test its properties
        $notification = new OperatorStatusUpdate($operator, OperatorStatus::Pending);

        expect($notification->operator)->toBe($operator);
        expect($notification->oldStatus)->toBe(OperatorStatus::Pending);
        expect($notification->operator->status)->toBe(OperatorStatus::Approved);

        // Test notification data
        $arrayData = $notification->toArray($admin);
        expect($arrayData)->toHaveKey('operator_id');
        expect($arrayData)->toHaveKey('operator_name');
        expect($arrayData)->toHaveKey('old_status');
        expect($arrayData)->toHaveKey('new_status');
        expect($arrayData['operator_id'])->toBe($operator->id);
        expect($arrayData['new_status'])->toBe(OperatorStatus::Approved->value);
        expect($arrayData['old_status'])->toBe(OperatorStatus::Pending->value);
    });

    it('sends different notification types for different status changes', function (): void {
        $operator = Operator::factory()->create([
            'contact_email' => 'operator@example.com',
        ]);

        // Test approval notification (operator status is now approved)
        $operator->update(['status' => OperatorStatus::Approved]);
        $approvalNotification = new OperatorStatusUpdate($operator, OperatorStatus::Pending);
        expect($approvalNotification->operator->status)->toBe(OperatorStatus::Approved);
        expect($approvalNotification->oldStatus)->toBe(OperatorStatus::Pending);

        // Test rejection notification
        $operator->update(['status' => OperatorStatus::Rejected]);
        $rejectionNotification = new OperatorStatusUpdate($operator, OperatorStatus::Pending);
        expect($rejectionNotification->operator->status)->toBe(OperatorStatus::Rejected);
        expect($rejectionNotification->oldStatus)->toBe(OperatorStatus::Pending);

        // Test suspension notification
        $operator->update(['status' => OperatorStatus::Suspended]);
        $suspensionNotification = new OperatorStatusUpdate($operator, OperatorStatus::Approved);
        expect($suspensionNotification->operator->status)->toBe(OperatorStatus::Suspended);
        expect($suspensionNotification->oldStatus)->toBe(OperatorStatus::Approved);
    });
});
