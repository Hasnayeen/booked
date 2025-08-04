<?php

declare(strict_types=1);

namespace App\ValueObjects;

class SeatPosition
{
    public function __construct(
        public readonly string $seatNumber,
        public readonly int $row,
        public readonly int $column,
        public readonly string $rowLabel,
        public readonly string $columnLabel,
        public readonly bool $isAvailable = true,
        public readonly int $priceInCents = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            seatNumber: $data['seat_number'],
            row: $data['row'],
            column: $data['column'],
            rowLabel: $data['row_label'],
            columnLabel: $data['column_label'],
            isAvailable: $data['is_available'] ?? true,
            priceInCents: $data['price_in_cents'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'seat_number' => $this->seatNumber,
            'row' => $this->row,
            'column' => $this->column,
            'row_label' => $this->rowLabel,
            'column_label' => $this->columnLabel,
            'is_available' => $this->isAvailable,
            'price_in_cents' => $this->priceInCents,
        ];
    }

    public function getDisplayName(): string
    {
        return $this->seatNumber;
    }

    public function isInRow(int $row): bool
    {
        return $this->row === $row;
    }

    public function isInColumn(int $column): bool
    {
        return $this->column === $column;
    }

    public function getPriceFormatted(string $currency = 'USD'): string
    {
        $amount = $this->priceInCents / 100;

        return number_format($amount, 2) . ' ' . $currency;
    }
}
