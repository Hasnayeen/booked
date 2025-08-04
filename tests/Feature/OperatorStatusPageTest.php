<?php

declare(strict_types=1);

use App\Enums\OperatorStatus;
use App\Filament\Operator\Pages\Tenancy\OperatorStatusPage;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;

use function Pest\Livewire\livewire;

describe('OperatorStatusPage Infolist', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()->create();
        $this->staffRole = Role::where('name', 'Operator Staff')->first();

        $this->actingAs($this->user);
    });

    it('can render operator status infolist', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Approved,
            'name' => 'Test Operator',
            'contact_email' => 'test@example.com',
            'contact_phone' => '123-456-7890',
        ]);

        // Associate user with operator
        $this->user->operators()->attach($operator, ['role_id' => $this->staffRole->id]);

        filament()->setTenant($operator);

        livewire(OperatorStatusPage::class, ['operator' => $operator])
            ->assertSuccessful()
            ->assertSee('✅ Operator Approved')
            ->assertSee('Test Operator')
            ->assertSee('test@example.com')
            ->assertSee('123-456-7890');
    });

    it('shows correct status for pending operator', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Pending,
            'name' => 'Pending Operator',
        ]);

        // Associate user with operator
        $this->user->operators()->attach($operator, ['role_id' => $this->staffRole->id]);

        filament()->setTenant($operator);

        livewire(OperatorStatusPage::class, ['operator' => $operator])
            ->assertSuccessful()
            ->assertSee('⏳ Registration Under Review')
            ->assertSee('Your operator registration is currently being reviewed by our team. We will notify you via email once a decision has been made.');
    });

    it('shows correct status for suspended operator', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Suspended,
            'name' => 'Suspended Operator',
        ]);

        // Associate user with operator
        $this->user->operators()->attach($operator, ['role_id' => $this->staffRole->id]);

        filament()->setTenant($operator);

        livewire(OperatorStatusPage::class, ['operator' => $operator])
            ->assertSuccessful()
            ->assertSee('⚠️ Account Suspended')
            ->assertSee('Your operator account has been temporarily suspended. Please contact support for assistance in resolving this issue.');
    });

    it('shows correct status for rejected operator', function (): void {
        $operator = Operator::factory()->create([
            'status' => OperatorStatus::Rejected,
            'name' => 'Rejected Operator',
        ]);

        // Associate user with operator
        $this->user->operators()->attach($operator, ['role_id' => $this->staffRole->id]);

        filament()->setTenant($operator);

        livewire(OperatorStatusPage::class, ['operator' => $operator])
            ->assertSuccessful()
            ->assertSee('❌ Registration Rejected')
            ->assertSee('Unfortunately, your operator registration could not be approved at this time. Please review the feedback and consider reapplying.');
    });

    it('can not access page when user does not belong to operator tenant', function (): void {
        $otherOperator = Operator::factory()->create(['status' => OperatorStatus::Approved]);
        filament()->setTenant($otherOperator);

        livewire(OperatorStatusPage::class, ['operator' => $otherOperator])
            ->assertForbidden();
    });
});
