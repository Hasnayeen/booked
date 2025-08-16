<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SeatPicker extends Field
{
    protected string $view = 'filament.forms.components.seat-picker';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
    }
}
