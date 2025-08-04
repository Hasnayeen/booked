<?php

declare(strict_types=1);

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Filament\Operator\Pages\Tenancy\RegisterOperator;
use App\Models\Operator;
use App\Models\User;
use App\Notifications\OperatorRegistrationConfirmation;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

describe('Operator Registration', function (): void {
    beforeEach(function (): void {
        Notification::fake();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        filament()->setCurrentPanel('operator');
    });

    it('can register an operator with required information', function (): void {
        $newOperatorData = Operator::factory()->make([
            'name' => 'Sunshine Hotel Group',
            'type' => OperatorType::Hotel,
            'contact_email' => 'contact@sunshinehotels.com',
        ]);

        livewire(RegisterOperator::class)
            ->fillForm([
                'name' => $newOperatorData->name,
                'type' => $newOperatorData->type->value,
                'contact_email' => $newOperatorData->contact_email,
            ])
            ->call('register')
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('operators', [
            'name' => $newOperatorData->name,
            'type' => $newOperatorData->type->value,
            'status' => OperatorStatus::Pending->value,
            'contact_email' => $newOperatorData->contact_email,
        ]);
    });

    it('can register an operator with optional information', function (): void {
        $newOperatorData = Operator::factory()->make([
            'name' => 'Express Bus Lines',
            'type' => OperatorType::Bus,
            'contact_email' => 'info@expressbuslines.com',
            'contact_phone' => '+1-555-123-4567',
            'description' => 'Premium intercity bus service connecting major cities.',
        ]);

        livewire(RegisterOperator::class)
            ->fillForm([
                'name' => $newOperatorData->name,
                'type' => $newOperatorData->type->value,
                'contact_email' => $newOperatorData->contact_email,
                'contact_phone' => $newOperatorData->contact_phone,
                'description' => $newOperatorData->description,
            ])
            ->call('register')
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('operators', [
            'name' => $newOperatorData->name,
            'type' => $newOperatorData->type->value,
            'status' => OperatorStatus::Pending->value,
            'contact_email' => $newOperatorData->contact_email,
            'contact_phone' => $newOperatorData->contact_phone,
            'description' => $newOperatorData->description,
        ]);
    });

    it('sends confirmation notification to user after registration', function (): void {
        $newOperatorData = Operator::factory()->make([
            'name' => 'City Transport Co.',
            'type' => OperatorType::Bus,
            'contact_email' => 'admin@citytransport.com',
        ]);

        livewire(RegisterOperator::class)
            ->fillForm([
                'name' => $newOperatorData->name,
                'type' => $newOperatorData->type->value,
                'contact_email' => $newOperatorData->contact_email,
            ])
            ->call('register');

        Notification::assertSentTo(
            $this->user,
            OperatorRegistrationConfirmation::class,
        );
    });

    it('requires name field for registration', function (): void {
        livewire(RegisterOperator::class)
            ->fillForm([
                'name' => '',
                'type' => OperatorType::Hotel->value,
                'contact_email' => 'contact@example.com',
            ])
            ->call('register')
            ->assertHasFormErrors(['name']);
    });

    it('requires valid email for contact_email field', function (): void {
        livewire(RegisterOperator::class)
            ->fillForm([
                'name' => 'Test Hotel',
                'type' => OperatorType::Hotel->value,
                'contact_email' => 'invalid-email',
            ])
            ->call('register')
            ->assertHasFormErrors(['contact_email']);
    });

    it('requires operator type selection', function (): void {
        livewire(RegisterOperator::class)
            ->fillForm([
                'name' => 'Test Company',
                'type' => '',
                'contact_email' => 'contact@example.com',
            ])
            ->call('register')
            ->assertHasFormErrors(['type']);
    });
});
