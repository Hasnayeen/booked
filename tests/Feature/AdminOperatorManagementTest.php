<?php

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Filament\Admin\Resources\Operators\Pages\EditOperator;
use App\Filament\Admin\Resources\Operators\Pages\ListOperators;
use App\Filament\Admin\Resources\Operators\Pages\ViewOperator;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use App\Notifications\OperatorStatusUpdate;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

describe('Admin Operator Management', function (): void {
    beforeEach(function (): void {
        Notification::fake();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $adminRole = Role::where(['name' => 'Admin'])->first();

        $this->admin = User::factory()->create([
            'email' => 'admin@booked.com',
            'email_verified_at' => now(),
        ]);
        $this->admin->roles()->attach($adminRole);

        $this->actingAs($this->admin);
    });

    it('can list all operators in admin panel', function (): void {
        $operators = Operator::factory()->count(3)->create([
            'status' => OperatorStatus::PENDING,
        ]);

        livewire(ListOperators::class)
            ->assertOk()
            ->assertCanSeeTableRecords($operators);
    });

    it('can view operator details', function (): void {
        $operator = Operator::factory()->create([
            'name' => 'Sunshine Hotels',
            'type' => OperatorType::HOTEL,
            'status' => OperatorStatus::PENDING,
            'contact_email' => 'info@sunshine.com',
            'contact_phone' => '+1-555-0123',
            'description' => 'Luxury hotel chain',
        ]);

        livewire(ViewOperator::class, [
            'record' => $operator->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $operator->name,
                'type' => $operator->type->value,
                'status' => $operator->status->value,
                'contact_email' => $operator->contact_email,
                'contact_phone' => $operator->contact_phone,
                'description' => $operator->description,
            ]);
    });

    it('can approve pending operator registration', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::PENDING,
            'contact_email' => 'operator@example.com',
        ]);

        livewire(EditOperator::class, [
            'record' => $operator->id,
        ])
            ->fillForm([
                'status' => OperatorStatus::APPROVED->value,
            ])
            ->call('save')
            ->assertNotified();

        expect($operator->fresh()->status)->toBe(OperatorStatus::APPROVED);

        Notification::assertSentTo(
            [$operator->contact_email],
            OperatorStatusUpdate::class,
            fn($notification): bool => $notification->operator->id === $operator->id
                && $notification->newStatus === OperatorStatus::APPROVED,
        );
    });

    it('can reject operator registration', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::PENDING,
            'contact_email' => 'operator@example.com',
        ]);

        livewire(EditOperator::class, [
            'record' => $operator->id,
        ])
            ->fillForm([
                'status' => OperatorStatus::REJECTED->value,
            ])
            ->call('save')
            ->assertNotified();

        expect($operator->fresh()->status)->toBe(OperatorStatus::REJECTED);

        Notification::assertSentTo(
            [$operator->contact_email],
            OperatorStatusUpdate::class,
            fn($notification): bool => $notification->operator->id === $operator->id
                && $notification->newStatus === OperatorStatus::REJECTED,
        );
    });

    it('can suspend approved operator', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::APPROVED,
            'contact_email' => 'operator@example.com',
        ]);

        livewire(EditOperator::class, [
            'record' => $operator->id,
        ])
            ->fillForm([
                'status' => OperatorStatus::SUSPENDED->value,
            ])
            ->call('save')
            ->assertNotified();

        expect($operator->fresh()->status)->toBe(OperatorStatus::SUSPENDED);

        Notification::assertSentTo(
            [$operator->contact_email],
            OperatorStatusUpdate::class,
            fn($notification): bool => $notification->operator->id === $operator->id
                && $notification->newStatus === OperatorStatus::SUSPENDED,
        );
    });

    it('can filter operators by status', function (): void {
        $pendingOperators = Operator::factory()->count(2)->create([
            'status' => OperatorStatus::PENDING,
        ]);

        $approvedOperators = Operator::factory()->count(3)->create([
            'status' => OperatorStatus::APPROVED,
        ]);

        livewire(ListOperators::class)
            ->filterTable('status', OperatorStatus::PENDING->value)
            ->assertCanSeeTableRecords($pendingOperators)
            ->assertCannotSeeTableRecords($approvedOperators);
    });

    it('can filter operators by type', function (): void {
        $hotelOperators = Operator::factory()->count(2)->create([
            'type' => OperatorType::HOTEL,
        ]);

        $busOperators = Operator::factory()->count(3)->create([
            'type' => OperatorType::BUS,
        ]);

        livewire(ListOperators::class)
            ->filterTable('type', OperatorType::HOTEL->value)
            ->assertCanSeeTableRecords($hotelOperators)
            ->assertCannotSeeTableRecords($busOperators);
    });
})->todo('Implement operator management tests for admin panel');
