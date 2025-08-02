<?php

namespace App\ValueObjects;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class SeatConfiguration
{
    public function __construct(
        public readonly string $deckType, // '1' for single, '2' for double
        public readonly SeatDeck $lowerDeck,
        public readonly ?SeatDeck $upperDeck = null,
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        throw_if(! isset($data['deck_type']) || ! isset($data['lower_deck']), new InvalidArgumentException('Seat configuration must have deck_type and lower_deck'));

        $lowerDeck = SeatDeck::fromArray($data['lower_deck']);
        $upperDeck = null;

        if ($data['deck_type'] === '2' && isset($data['upper_deck'])) {
            $upperDeck = SeatDeck::fromArray($data['upper_deck']);
        }

        return new self(
            deckType: $data['deck_type'],
            lowerDeck: $lowerDeck,
            upperDeck: $upperDeck,
        );
    }

    public static function fromFormData(array $formData): self
    {
        $lowerDeck = new SeatDeck(
            seatType: $formData['seat_type'] ?? '1',
            totalColumns: (int) ($formData['total_columns'] ?? 4),
            columnLabel: $formData['column_label'] ?? 'alpha',
            columnLayout: $formData['column_layout'] ?? '2:2',
            totalRows: (int) ($formData['total_rows'] ?? 5),
            rowLabel: $formData['row_label'] ?? 'numeric',
            pricePerSeatInCents: (int) (($formData['price_per_seat'] ?? 0) * 100),
        );

        $upperDeck = null;
        if (($formData['deck'] ?? '1') === '2') {
            $upperDeck = new SeatDeck(
                seatType: $formData['seat_type_upper'] ?? '1',
                totalColumns: (int) ($formData['total_columns_upper'] ?? 4),
                columnLabel: $formData['column_label_upper'] ?? 'alpha',
                columnLayout: $formData['column_layout_upper'] ?? '2:2',
                totalRows: (int) ($formData['total_rows_upper'] ?? 5),
                rowLabel: $formData['row_label_upper'] ?? 'numeric',
                pricePerSeatInCents: (int) (($formData['price_per_seat_upper'] ?? 0) * 100),
            );
        }

        return new self(
            deckType: $formData['deck'] ?? '1',
            lowerDeck: $lowerDeck,
            upperDeck: $upperDeck,
        );
    }

    public function toArray(): array
    {
        $data = [
            'deck_type' => $this->deckType,
            'lower_deck' => $this->lowerDeck->toArray(),
        ];

        if ($this->upperDeck instanceof SeatDeck) {
            $data['upper_deck'] = $this->upperDeck->toArray();
        }

        return $data;
    }

    public function toFormData(): array
    {
        $data = [
            'deck' => $this->deckType,
            'seat_type' => $this->lowerDeck->seatType,
            'total_columns' => $this->lowerDeck->totalColumns,
            'column_label' => $this->lowerDeck->columnLabel,
            'column_layout' => $this->lowerDeck->columnLayout,
            'total_rows' => $this->lowerDeck->totalRows,
            'row_label' => $this->lowerDeck->rowLabel,
            'price_per_seat' => $this->lowerDeck->pricePerSeatInCents / 100,
        ];

        if ($this->upperDeck instanceof SeatDeck) {
            return array_merge($data, [
                'seat_type_upper' => $this->upperDeck->seatType,
                'total_columns_upper' => $this->upperDeck->totalColumns,
                'column_label_upper' => $this->upperDeck->columnLabel,
                'column_layout_upper' => $this->upperDeck->columnLayout,
                'total_rows_upper' => $this->upperDeck->totalRows,
                'row_label_upper' => $this->upperDeck->rowLabel,
                'price_per_seat_upper' => $this->upperDeck->pricePerSeatInCents / 100,
            ]);
        }

        return $data;
    }

    private function validate(): void
    {
        throw_if($this->deckType === '2' && ! $this->upperDeck instanceof SeatDeck, new InvalidArgumentException('Double deck buses must have an upper deck configuration'));

        throw_if($this->deckType === '1' && $this->upperDeck instanceof SeatDeck, new InvalidArgumentException('Single deck buses cannot have an upper deck configuration'));
    }

    public function getAllSeats(): Collection
    {
        $seats = $this->lowerDeck->getSeats();

        if ($this->upperDeck instanceof SeatDeck) {
            return $seats->merge($this->upperDeck->getSeats());
        }

        return $seats;
    }

    public function getTotalSeats(): int
    {
        $total = $this->lowerDeck->getTotalSeats();

        if ($this->upperDeck instanceof SeatDeck) {
            $total += $this->upperDeck->getTotalSeats();
        }

        return $total;
    }

    public function getAvailableSeats(): Collection
    {
        return $this->getAllSeats()->filter(fn ($seat) => $seat->isAvailable);
    }

    public function findSeat(string $seatNumber): ?SeatPosition
    {
        $seat = $this->lowerDeck->findSeat($seatNumber);

        if (! $seat instanceof SeatPosition && $this->upperDeck instanceof SeatDeck) {
            return $this->upperDeck->findSeat($seatNumber);
        }

        return $seat;
    }

    public function isDoubleDeck(): bool
    {
        return $this->deckType === '2';
    }

    public function hasUpperDeck(): bool
    {
        return $this->upperDeck instanceof SeatDeck;
    }

    public function getLowerDeckSeats(): Collection
    {
        return $this->lowerDeck->getSeats();
    }

    public function getUpperDeckSeats(): ?Collection
    {
        return $this->upperDeck?->getSeats();
    }

    public function getSeatsInDeck(string $deck): Collection
    {
        return match ($deck) {
            'lower' => $this->getLowerDeckSeats(),
            'upper' => $this->getUpperDeckSeats() ?? collect(),
            default => collect()
        };
    }

    public function validateTotalSeats(int $expectedTotal): bool
    {
        return $this->getTotalSeats() === $expectedTotal;
    }

    public function getBasePriceInCents(): int
    {
        $lowerPrice = $this->lowerDeck->pricePerSeatInCents;

        if ($this->upperDeck instanceof SeatDeck) {
            return min($lowerPrice, $this->upperDeck->pricePerSeatInCents);
        }

        return $lowerPrice;
    }

    public function getMaxPriceInCents(): int
    {
        $lowerPrice = $this->lowerDeck->pricePerSeatInCents;

        if ($this->upperDeck instanceof SeatDeck) {
            return max($lowerPrice, $this->upperDeck->pricePerSeatInCents);
        }

        return $lowerPrice;
    }

    public function getAllPricesInCents(): array
    {
        $prices = [$this->lowerDeck->pricePerSeatInCents];

        if ($this->upperDeck instanceof SeatDeck) {
            $prices[] = $this->upperDeck->pricePerSeatInCents;
        }

        return $prices;
    }
}
