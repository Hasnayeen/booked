<?php

declare(strict_types=1);

use App\Filament\Forms\Components\CreditCardNumberInput;

it('can create credit card number input component', function (): void {
    $component = CreditCardNumberInput::make('card_number');

    expect($component)->toBeInstanceOf(CreditCardNumberInput::class);
});

it('has default masked behavior', function (): void {
    $component = CreditCardNumberInput::make('card_number');

    expect($component->isMasked())->toBeTrue();
});

it('can be unmasked', function (): void {
    $component = CreditCardNumberInput::make('card_number')->unmasked();

    expect($component->isMasked())->toBeFalse();
});

it('can be explicitly masked', function (): void {
    $component = CreditCardNumberInput::make('card_number')->masked();

    expect($component->isMasked())->toBeTrue();
});

it('validates credit card number format', function (): void {
    $component = CreditCardNumberInput::make('card_number');
    $rules = $component->getValidationRules();

    expect($rules)->toContain('string');
    expect($rules)->toContain('regex:/^[0-9]{16}$/');
});

it('can be set as readonly', function (): void {
    $component = CreditCardNumberInput::make('card_number')->readOnly();

    expect($component->isReadOnly())->toBeTrue();
});

it('is not readonly by default', function (): void {
    $component = CreditCardNumberInput::make('card_number');

    expect($component->isReadOnly())->toBeFalse();
});
