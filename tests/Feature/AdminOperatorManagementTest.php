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
            'status' => OperatorStatus::Pending,
        ]);

        livewire(ListOperators::class)
            ->assertOk()
            ->assertCanSeeTableRecords($operators);
    });

    it('can view operator details', function (): void {
        $operator = Operator::factory()->create([
            'name' => 'Sunshine Hotels',
            'type' => OperatorType::Hotel,
            'status' => OperatorStatus::Pending,
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
            'status' => OperatorStatus::Pending,
            'contact_email' => 'operator@example.com',
        ]);

        livewire(EditOperator::class, [
            'record' => $operator->id,
        ])
            ->fillForm([
                'status' => OperatorStatus::Approved->value,
            ])
            ->call('save')
            ->assertNotified();

        expect($operator->fresh()->status)->toBe(OperatorStatus::Approved);

        Notification::assertSentTo(
            [$operator->contact_email],
            OperatorStatusUpdate::class,
            fn ($notification): bool => $notification->operator->id === $operator->id
                && $notification->newStatus === OperatorStatus::Approved,
        );
    });

    it('can reject operator registration', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Pending,
            'contact_email' => 'operator@example.com',
        ]);

        livewire(EditOperator::class, [
            'record' => $operator->id,
        ])
            ->fillForm([
                'status' => OperatorStatus::Rejected->value,
            ])
            ->call('save')
            ->assertNotified();

        expect($operator->fresh()->status)->toBe(OperatorStatus::Rejected);

        Notification::assertSentTo(
            [$operator->contact_email],
            OperatorStatusUpdate::class,
            fn ($notification): bool => $notification->operator->id === $operator->id
                && $notification->newStatus === OperatorStatus::Rejected,
        );
    });

    it('can suspend approved operator', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Approved,
            'contact_email' => 'operator@example.com',
        ]);

        livewire(EditOperator::class, [
            'record' => $operator->id,
        ])
            ->fillForm([
                'status' => OperatorStatus::Suspended->value,
            ])
            ->call('save')
            ->assertNotified();

        expect($operator->fresh()->status)->toBe(OperatorStatus::Suspended);

        Notification::assertSentTo(
            [$operator->contact_email],
            OperatorStatusUpdate::class,
            fn ($notification): bool => $notification->operator->id === $operator->id
                && $notification->newStatus === OperatorStatus::Suspended,
        );
    });

    it('can filter operators by status', function (): void {
        $pendingOperators = Operator::factory()->count(2)->create([
            'status' => OperatorStatus::Pending,
        ]);

        $approvedOperators = Operator::factory()->count(3)->create([
            'status' => OperatorStatus::Approved,
        ]);

        livewire(ListOperators::class)
            ->filterTable('status', OperatorStatus::Pending->value)
            ->assertCanSeeTableRecords($pendingOperators)
            ->assertCannotSeeTableRecords($approvedOperators);
    });

    it('can filter operators by type', function (): void {
        $hotelOperators = Operator::factory()->count(2)->create([
            'type' => OperatorType::Hotel,
        ]);

        $busOperators = Operator::factory()->count(3)->create([
            'type' => OperatorType::Bus,
        ]);

        livewire(ListOperators::class)
            ->filterTable('type', OperatorType::Hotel->value)
            ->assertCanSeeTableRecords($hotelOperators)
            ->assertCannotSeeTableRecords($busOperators);
    });
})->todo('Implement operator management tests for admin panel');
