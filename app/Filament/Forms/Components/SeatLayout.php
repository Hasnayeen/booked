<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Models\RouteSchedule;
use App\ValueObjects\SeatConfiguration;
use Closure;
use Filament\Forms\Components\Field;

class SeatLayout extends Field
{
    protected string $view = 'filament.forms.components.seat-layout';

    protected RouteSchedule|Closure|null $routeSchedule = null;

    protected string|Closure|null $travelDate = null;

    protected int|Closure|null $passengerCount = null;

    protected bool|Closure $allowMultipleSelection = true;

    public function routeSchedule(RouteSchedule|Closure|null $routeSchedule): static
    {
        $this->routeSchedule = $routeSchedule;

        return $this;
    }

    public function travelDate(string|Closure|null $travelDate): static
    {
        $this->travelDate = $travelDate;

        return $this;
    }

    public function passengerCount(int|Closure|null $passengerCount): static
    {
        $this->passengerCount = $passengerCount;

        return $this;
    }

    public function allowMultipleSelection(bool|Closure $allowMultipleSelection = true): static
    {
        $this->allowMultipleSelection = $allowMultipleSelection;

        return $this;
    }

    public function getRouteSchedule(): ?RouteSchedule
    {
        return $this->evaluate($this->routeSchedule);
    }

    public function getTravelDate(): ?string
    {
        return $this->evaluate($this->travelDate);
    }

    public function getPassengerCount(): ?int
    {
        return $this->evaluate($this->passengerCount);
    }

    public function getAllowMultipleSelection(): bool
    {
        return $this->evaluate($this->allowMultipleSelection);
    }

    public function getSeatConfiguration(): ?SeatConfiguration
    {
        $routeSchedule = $this->getRouteSchedule();
        $travelDate = $this->getTravelDate();

        if (! $routeSchedule instanceof RouteSchedule || ($travelDate === null || $travelDate === '' || $travelDate === '0')) {
            return null;
        }

        return $routeSchedule->bus->getSeatConfigurationForDate($travelDate, $routeSchedule->id);
    }
}
