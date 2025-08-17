<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\CanBeReadOnly;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class CreditCardNumberInput extends Field
{
    use CanBeReadOnly;
    use HasExtraAlpineAttributes;
    use HasPlaceholder;

    protected string $view = 'filament.forms.components.credit-card-number-input';

    protected bool|Closure $isMasked = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule('string');
        $this->rule('regex:/^[0-9]{16}$/');
    }

    public function masked(bool|Closure $condition = true): static
    {
        $this->isMasked = $condition;

        return $this;
    }

    public function unmasked(bool|Closure $condition = true): static
    {
        $this->isMasked = ! $condition;

        return $this;
    }

    public function isMasked(): bool
    {
        return (bool) $this->evaluate($this->isMasked);
    }
}
