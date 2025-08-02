<?php

namespace Database\Factories;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Models\Bus;
use App\Models\Operator;
use App\ValueObjects\SeatConfiguration;
use App\ValueObjects\SeatDeck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bus>
 */
class BusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Bus::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $totalSeats = fake()->numberBetween(20, 60);

        return [
            'operator_id' => Operator::factory(),
            'bus_number' => fake()->unique()->bothify('BUS-###??'),
            'category' => fake()->randomElement(BusCategory::cases()),
            'type' => fake()->randomElement(BusType::cases()),
            'total_seats' => $totalSeats,
            'license_plate' => fake()->unique()->bothify('??-##-???'),
            'is_active' => fake()->boolean(85), // 85% chance of being active
            'seat_config' => $this->generateSeatConfiguration($totalSeats),
            'amenities' => fake()->randomElements([
                'WiFi',
                'Air Conditioning',
                'Comfortable Seats',
                'Entertainment System',
                'USB Charging Ports',
                'Reading Lights',
                'Blankets',
                'Water Bottle',
                'Snacks',
                'Restroom',
                'GPS Tracking',
                'CCTV Security',
            ], fake()->numberBetween(2, 6)),
            'metadata' => [
                'manufacturer' => fake()->randomElement(['Volvo', 'Mercedes', 'Scania', 'MAN', 'Isuzu']),
                'model' => fake()->word(),
                'year' => fake()->numberBetween(2015, 2024),
                'fuel_type' => fake()->randomElement(['Diesel', 'CNG', 'Electric']),
                'mileage' => fake()->numberBetween(50000, 500000),
                'last_service_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Generate a realistic seat configuration based on total seats.
     */
    private function generateSeatConfiguration(int $totalSeats): SeatConfiguration
    {
        $deckType = fake()->boolean(20) ? '2' : '1'; // 20% chance of double deck
        $seatType = fake()->randomElement(['1', '2']); // 1 = seat, 2 = sleeper
        $columnLabel = fake()->randomElement(['alpha', 'numeric']);
        $rowLabel = fake()->randomElement(['alpha', 'numeric']);

        // Different layouts based on seat count
        $layouts = ['2:2', '1:2', '2:1', '1:1'];
        $columnLayout = fake()->randomElement($layouts);

        // Calculate rows and columns to approximate total seats
        $columns = $this->getColumnsFromLayout($columnLayout);
        $rows = max(1, (int) ceil($totalSeats / $columns));

        // Base price varies by seat type
        $basePrice = $seatType === '2' ?
            fake()->numberBetween(80000, 150000) : // Sleeper: $800-$1500
            fake()->numberBetween(30000, 80000);   // Regular: $300-$800

        $lowerDeck = new SeatDeck(
            seatType: $seatType,
            totalColumns: $columns,
            columnLabel: $columnLabel,
            columnLayout: $columnLayout,
            totalRows: $rows,
            rowLabel: $rowLabel,
            pricePerSeatInCents: $basePrice,
        );

        $upperDeck = null;
        if ($deckType === '2') {
            // Upper deck typically has fewer seats and may be more expensive
            $upperRows = max(1, (int) ceil($rows * 0.6)); // 60% of lower deck
            $upperPrice = (int) ($basePrice * fake()->randomFloat(2, 1.1, 1.5)); // 10-50% more expensive

            $upperDeck = new SeatDeck(
                seatType: $seatType,
                totalColumns: $columns,
                columnLabel: $columnLabel,
                columnLayout: $columnLayout,
                totalRows: $upperRows,
                rowLabel: $rowLabel,
                pricePerSeatInCents: $upperPrice,
            );
        }

        return new SeatConfiguration(
            deckType: $deckType,
            lowerDeck: $lowerDeck,
            upperDeck: $upperDeck,
        );
    }

    /**
     * Get total columns from layout string.
     */
    private function getColumnsFromLayout(string $layout): int
    {
        $parts = explode(':', $layout);

        return array_sum(array_map('intval', $parts));
    }

    /**
     * Create an AC bus.
     */
    public function ac(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => BusType::Ac,
        ]);
    }

    /**
     * Create a Non-AC bus.
     */
    public function nonAc(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => BusType::NonAc,
        ]);
    }

    /**
     * Create a luxury bus.
     */
    public function luxury(): static
    {
        return $this->state(function (array $attributes): array {
            $totalSeats = fake()->numberBetween(20, 40); // Luxury buses have fewer seats

            // Luxury buses often have 1:1 or 1:2 layout for more space
            $seatConfig = new SeatConfiguration(
                deckType: '1', // Usually single deck for luxury
                lowerDeck: new SeatDeck(
                    seatType: '1', // Regular seats, not sleeper
                    totalColumns: 3, // 1:2 layout
                    columnLabel: 'alpha',
                    columnLayout: '1:2',
                    totalRows: (int) ceil($totalSeats / 3),
                    rowLabel: 'numeric',
                    pricePerSeatInCents: fake()->numberBetween(120000, 200000), // $1200-$2000
                ),
                upperDeck: null, // Single deck, so no upper deck
            );

            return [
                'category' => BusCategory::Luxury,
                'total_seats' => $totalSeats,
                'seat_config' => $seatConfig,
                'amenities' => [
                    'WiFi',
                    'Air Conditioning',
                    'Reclining Seats',
                    'Entertainment System',
                    'USB Charging Ports',
                    'Reading Lights',
                    'Blankets',
                    'Refreshments',
                    'Restroom',
                    'GPS Tracking',
                    'CCTV Security',
                    'Personal Attendant',
                ],
            ];
        });
    }

    /**
     * Create a sleeper bus.
     */
    public function sleeper(): static
    {
        return $this->state(function (array $attributes): array {
            $totalSeats = fake()->numberBetween(24, 36); // Sleeper buses have berths

            // Sleeper buses typically have 2:1 or 1:2 layout for berths
            $seatConfig = new SeatConfiguration(
                deckType: $doubleDecker = fake()->boolean(70) ? '2' : '1', // 70% chance of double deck for sleepers
                lowerDeck: new SeatDeck(
                    seatType: '2', // Sleeper berths
                    totalColumns: 3, // 2:1 or 1:2 layout
                    columnLabel: 'alpha',
                    columnLayout: fake()->randomElement(['2:1', '1:2']),
                    totalRows: (int) ceil($totalSeats / 3),
                    rowLabel: 'numeric',
                    pricePerSeatInCents: fake()->numberBetween(80000, 120000), // $800-$1200
                ),
                upperDeck: $doubleDecker !== '' && $doubleDecker !== '0' ? new SeatDeck(
                    seatType: '2',
                    totalColumns: 3,
                    columnLabel: 'alpha',
                    columnLayout: fake()->randomElement(['2:1', '1:2']),
                    totalRows: (int) ceil($totalSeats / 6), // Half the berths on upper deck
                    rowLabel: 'numeric',
                    pricePerSeatInCents: fake()->numberBetween(85000, 125000), // Slightly more expensive
                ) : null,
            );

            return [
                'category' => BusCategory::Sleeper,
                'total_seats' => $totalSeats,
                'seat_config' => $seatConfig,
                'amenities' => [
                    'Sleeping Berths',
                    'Air Conditioning',
                    'Privacy Curtains',
                    'Reading Lights',
                    'Storage Compartments',
                    'Blankets',
                    'Pillows',
                    'Restroom',
                    'GPS Tracking',
                    'CCTV Security',
                ],
            ];
        });
    }

    /**
     * Create a standard bus.
     */
    public function standard(): static
    {
        return $this->state(function (array $attributes): array {
            $totalSeats = fake()->numberBetween(40, 60); // Standard buses have more seats

            // Standard buses typically use 2:2 layout for maximum capacity
            $seatConfig = new SeatConfiguration(
                deckType: '1', // Usually single deck
                lowerDeck: new SeatDeck(
                    seatType: '1', // Regular seats
                    totalColumns: 4, // 2:2 layout
                    columnLabel: 'alpha',
                    columnLayout: '2:2',
                    totalRows: (int) ceil($totalSeats / 4),
                    rowLabel: 'numeric',
                    pricePerSeatInCents: fake()->numberBetween(30000, 60000), // $300-$600
                ),
                upperDeck: null, // Single deck, so no upper deck
            );

            return [
                'category' => BusCategory::Economy,
                'total_seats' => $totalSeats,
                'seat_config' => $seatConfig,
                'amenities' => [
                    'Comfortable Seats',
                    'Reading Lights',
                    'GPS Tracking',
                    'CCTV Security',
                ],
            ];
        });
    }

    /**
     * Create an active bus.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive bus.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a bus for a specific operator.
     */
    public function forOperator(Operator $operator): static
    {
        return $this->state(fn (array $attributes): array => [
            'operator_id' => $operator->id,
        ]);
    }
}
