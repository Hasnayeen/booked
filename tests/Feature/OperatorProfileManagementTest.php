<?php

declare(strict_types=1);

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Filament\Operator\Pages\Tenancy\EditOperatorProfile;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

describe('Operator Profile Management', function (): void {
    beforeEach(function (): void {
        Filament::setCurrentPanel(Filament::getPanel('operator'));

        $this->user = User::factory()->create([
            'email' => 'operator@booked.com',
            'email_verified_at' => now(),
        ]);

        $this->operator = Operator::factory()->create([
            'status' => OperatorStatus::Approved,
            'type' => OperatorType::Hotel,
            'name' => 'Test Hotel',
            'contact_email' => 'hotel@example.com',
            'contact_phone' => '+1234567890',
            'description' => 'A test hotel operator',
        ]);

        $this->role = Role::where([
            'name' => 'Operator Admin',
        ])->first();

        $this->operator->users()->attach($this->user, ['role_id' => $this->role->id, 'joined_at' => now()]);

        $this->actingAs($this->user);

        Filament::setTenant($this->operator);
    });

    it('can render the profile page', function (): void {
        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->assertSuccessful();
    });

    it('can load existing operator data in the form', function (): void {
        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->assertSchemaStateSet([
                'name' => 'Test Hotel',
                'type' => OperatorType::Hotel->value,
                'contact_email' => 'hotel@example.com',
                'contact_phone' => '+1234567890',
                'description' => 'A test hotel operator',
            ]);
    });

    it('can update operator profile', function (): void {
        $newData = [
            'name' => 'Updated Hotel Name',
            'type' => OperatorType::Bus->value,
            'contact_email' => 'updated@example.com',
            'contact_phone' => '+9876543210',
            'description' => 'Updated description for the operator',
        ];

        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->operator->refresh();

        expect($this->operator)
            ->name->toBe('Updated Hotel Name')
            ->contact_email->toBe('updated@example.com')
            ->contact_phone->toBe('+9876543210')
            ->description->toBe('Updated description for the operator');
    });

    it('can not change operator category', function (): void {
        $newData = [
            'name' => 'Updated Hotel Name',
            'type' => OperatorType::Bus->value,
            'contact_email' => 'updated@example.com',
            'contact_phone' => '+9876543210',
            'description' => 'Updated description for the operator',
        ];

        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->operator->refresh();

        expect($this->operator)
            ->type->not->toBe(OperatorType::Bus)
            ->type->toBe(OperatorType::Hotel);
    });

    it('validates required fields', function (): void {
        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm([
                'name' => '',
                'type' => '',
                'contact_email' => '',
                'contact_phone' => null,
                'description' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
                'type' => 'required',
                'contact_email' => 'required',
            ]);
    });

    it('validates email format', function (): void {
        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm([
                'contact_email' => 'invalid-email',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'contact_email' => 'email',
            ]);
    });

    it('validates unique email constraint', function (): void {
        // Create another operator with a different email
        $otherOperator = Operator::factory()->create([
            'contact_email' => 'other@example.com',
            'status' => OperatorStatus::Approved,
        ]);

        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm([
                'contact_email' => 'other@example.com',
            ])
            ->call('save')
            ->assertHasFormErrors([
                'contact_email' => 'unique',
            ]);
    });

    it('allows keeping the same email when updating', function (): void {
        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'contact_email' => 'hotel@example.com', // Same email as current
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->operator->refresh();
        expect($this->operator->name)->toBe('Updated Name');
    });

    it('validates maximum field lengths', function (): void {
        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm([
                'name' => str_repeat('a', 256), // Over 255 chars
                'contact_email' => str_repeat('a', 245) . '@example.com', // Over 255 chars
                'contact_phone' => str_repeat('1', 21), // Over 20 chars
                'description' => str_repeat('a', 1001), // Over 1000 chars
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'max',
                'contact_email' => 'max',
                'contact_phone' => 'max',
                'description' => 'max',
            ]);
    });

    it('displays success notification after successful update', function (): void {
        livewire(EditOperatorProfile::class, [
            'record' => $this->operator->getKey(),
        ])
            ->fillForm([
                'name' => 'Updated Hotel',
            ])
            ->call('save')
            ->assertNotified();
    });
});
