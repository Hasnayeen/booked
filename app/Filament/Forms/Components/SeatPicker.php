<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SeatPicker extends Field
{
    protected string $view = 'filament.forms.components.seat-picker';

    protected string $deck = 'lower';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
    }

    public function deck(string $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    public function getDeck(): string
    {
        return $this->evaluate($this->deck);
    }
}
