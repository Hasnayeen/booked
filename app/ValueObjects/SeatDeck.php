<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class SeatDeck
{
    public function __construct(
        public readonly string $seatType, // '1' for seat, '2' for sleeper
        public readonly int $totalColumns,
        public readonly string $columnLabel, // 'alpha' or 'numeric'
        public readonly string $columnLayout, // e.g., '2:2', '1:2'
        public readonly int $totalRows,
        public readonly string $rowLabel, // 'alpha' or 'numeric'
        public readonly int $pricePerSeatInCents,
        /** @var Collection<int, SeatPosition> */
        private readonly ?Collection $seats = null,
        public readonly int $rowOffset = 0,
        public readonly int $columnOffset = 0,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // Validate seat type
        throw_unless(in_array($this->seatType, ['1', '2']), new InvalidArgumentException('Seat type must be "1" (seat) or "2" (sleeper)'));

        // Validate columns constraints
        throw_if($this->totalColumns < 2 || $this->totalColumns > 4, new InvalidArgumentException('Total columns must be between 2 and 4'));

        // Validate rows constraints
        throw_if($this->totalRows < 5 || $this->totalRows > 10, new InvalidArgumentException('Total rows must be between 5 and 10'));

        // Validate column label
        throw_unless(in_array($this->columnLabel, ['alpha', 'numeric']), new InvalidArgumentException('Column label must be "alpha" or "numeric"'));

        // Validate row label
        throw_unless(in_array($this->rowLabel, ['alpha', 'numeric']), new InvalidArgumentException('Row label must be "alpha" or "numeric"'));

        // Validate column layout
        throw_unless(preg_match('/^\d+:\d+$/', $this->columnLayout), new InvalidArgumentException('Column layout must be in format "x:y" (e.g., "2:2")'));

        // Validate column layout matches total columns
        $layoutParts = explode(':', $this->columnLayout);
        $layoutTotal = (int) $layoutParts[0] + (int) $layoutParts[1];
        throw_if($layoutTotal !== $this->totalColumns, new InvalidArgumentException("Column layout ({$this->columnLayout}) must sum to total columns ({$this->totalColumns})"));

        // Validate price
        throw_if($this->pricePerSeatInCents < 0, new InvalidArgumentException('Price per seat must be non-negative'));
    }

    public static function fromArray(array $data): self
    {
        $seats = null;
        if (isset($data['seats']) && is_array($data['seats'])) {
            $seats = collect($data['seats'])->map(fn ($seat): SeatPosition => SeatPosition::fromArray($seat));
        }

        return new self(
            seatType: $data['seat_type'],
            totalColumns: $data['total_columns'],
            columnLabel: $data['column_label'],
            columnLayout: $data['column_layout'],
            totalRows: $data['total_rows'],
            rowLabel: $data['row_label'],
            pricePerSeatInCents: $data['price_per_seat_in_cents'],
            seats: $seats,
            rowOffset: $data['row_offset'] ?? 0,
            columnOffset: $data['column_offset'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'seat_type' => $this->seatType,
            'total_columns' => $this->totalColumns,
            'column_label' => $this->columnLabel,
            'column_layout' => $this->columnLayout,
            'total_rows' => $this->totalRows,
            'row_label' => $this->rowLabel,
            'price_per_seat_in_cents' => $this->pricePerSeatInCents,
            'row_offset' => $this->rowOffset,
            'column_offset' => $this->columnOffset,
            'seats' => $this->getSeats()->map(fn ($seat) => $seat->toArray())->toArray(),
        ];
    }

    public function getSeats(): Collection
    {
        if ($this->seats instanceof Collection) {
            return $this->seats;
        }

        return $this->generateSeats();
    }

    public function generateSeats(): Collection
    {
        $seats = collect();
        $columnLabels = $this->generateColumnLabels();
        $rowLabels = $this->generateRowLabels();
        [$leftColumns, $rightColumns] = $this->parseColumnLayout();

        for ($row = 1; $row <= $this->totalRows; $row++) {
            // Left side seats
            for ($col = 1; $col <= $leftColumns; $col++) {
                // Ensure we don't exceed total columns
                if ($col <= $this->totalColumns) {
                    $seats->push($this->createSeat($row, $col, $rowLabels, $columnLabels));
                }
            }

            // Right side seats (offset by left columns + aisle)
            for ($col = 1; $col <= $rightColumns; $col++) {
                $actualCol = $leftColumns + $col;
                // Ensure we don't exceed total columns
                if ($actualCol <= $this->totalColumns) {
                    $seats->push($this->createSeat($row, $actualCol, $rowLabels, $columnLabels));
                }
            }
        }

        return $seats;
    }

    private function createSeat(int $row, int $column, array $rowLabels, array $columnLabels): SeatPosition
    {
        // Apply offsets to get the actual row and column numbers for labeling
        $actualRow = $row + $this->rowOffset;
        $actualColumn = $column + $this->columnOffset;

        // Handle edge cases where row/column exceeds available labels
        $rowIndex = min($actualRow - 1, count($rowLabels) - 1);
        $columnIndex = min($actualColumn - 1, count($columnLabels) - 1);

        $rowLabel = $rowLabels[$rowIndex];
        $columnLabel = $columnLabels[$columnIndex];

        // Generate seat number based on labeling preference
        $seatNumber = $this->rowLabel === 'numeric'
            ? $rowLabel . $columnLabel
            : $columnLabel . $rowLabel;

        return new SeatPosition(
            seatNumber: $seatNumber,
            row: $actualRow,
            column: $actualColumn,
            rowLabel: $rowLabel,
            columnLabel: $columnLabel,
            isAvailable: true,
            priceInCents: $this->pricePerSeatInCents,
        );
    }

    private function generateColumnLabels(): array
    {
        if ($this->columnLabel === 'numeric') {
            // Generate enough labels to accommodate the offset and total columns
            $maxNeeded = $this->columnOffset + $this->totalColumns;

            return array_map('strval', range(1, $maxNeeded));
        }

        // Generate enough alphabetical labels to accommodate the offset and total columns
        $maxNeeded = min($this->columnOffset + $this->totalColumns, 26);

        return array_slice(range('A', 'Z'), 0, $maxNeeded);
    }

    private function generateRowLabels(): array
    {
        if ($this->rowLabel === 'numeric') {
            // Generate enough labels to accommodate the offset and total rows
            $maxNeeded = $this->rowOffset + $this->totalRows;

            return array_map('strval', range(1, $maxNeeded));
        }

        // Generate enough alphabetical labels to accommodate the offset and total rows
        $maxNeeded = min($this->rowOffset + $this->totalRows, 26);

        return array_slice(range('A', 'Z'), 0, $maxNeeded);
    }

    private function parseColumnLayout(): array
    {
        $parts = explode(':', $this->columnLayout);

        return [(int) $parts[0], (int) $parts[1]];
    }

    public function getTotalSeats(): int
    {
        return $this->getSeats()->count();
    }

    public function getAvailableSeats(): Collection
    {
        return $this->getSeats()->filter(fn ($seat) => $seat->isAvailable);
    }

    public function getSeatsInRow(int $row): Collection
    {
        return $this->getSeats()->filter(fn ($seat) => $seat->isInRow($row));
    }

    public function findSeat(string $seatNumber): ?SeatPosition
    {
        return $this->getSeats()->first(fn ($seat): bool => $seat->seatNumber === $seatNumber);
    }

    public function isSleeper(): bool
    {
        return $this->seatType === '2';
    }

    public function getColumnLayoutDisplay(): string
    {
        [$left, $right] = $this->parseColumnLayout();

        return "{$left}:{$right} (Left: {$left}, Right: {$right})";
    }
}
