<?php

namespace App\ValueObjects;

use Illuminate\Support\Collection;

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
        private readonly ?Collection $seats = null,
    ) {}

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
        // Handle edge cases where row/column exceeds available labels
        $rowIndex = min($row - 1, count($rowLabels) - 1);
        $columnIndex = min($column - 1, count($columnLabels) - 1);

        $rowLabel = $rowLabels[$rowIndex];
        $columnLabel = $columnLabels[$columnIndex];

        // Generate seat number based on labeling preference
        $seatNumber = $this->rowLabel === 'numeric'
            ? $rowLabel . $columnLabel
            : $columnLabel . $rowLabel;

        return new SeatPosition(
            seatNumber: $seatNumber,
            row: $row,
            column: $column,
            rowLabel: $rowLabel,
            columnLabel: $columnLabel,
            isAvailable: true,
            priceInCents: $this->pricePerSeatInCents,
        );
    }

    private function generateColumnLabels(): array
    {
        if ($this->columnLabel === 'numeric') {
            return range(1, $this->totalColumns);
        }

        // Limit to 26 columns when using alphabetical labels
        $maxColumns = min($this->totalColumns, 26);

        return array_slice(range('A', 'Z'), 0, $maxColumns);
    }

    private function generateRowLabels(): array
    {
        if ($this->rowLabel === 'numeric') {
            return range(1, $this->totalRows);
        }

        // Limit to 26 rows when using alphabetical labels
        $maxRows = min($this->totalRows, 26);

        return array_slice(range('A', 'Z'), 0, $maxRows);
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
