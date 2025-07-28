<?php

namespace Database\Factories;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Models\Bus;
use App\Models\Operator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bus>
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
        return [
            'operator_id' => Operator::factory(),
            'bus_number' => $this->faker->unique()->bothify('BUS-###??'),
            'category' => $this->faker->randomElement(BusCategory::cases()),
            'type' => $this->faker->randomElement(BusType::cases()),
            'total_seats' => $this->faker->numberBetween(20, 60),
            'license_plate' => $this->faker->unique()->bothify('??-##-???'),
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
            'amenities' => $this->faker->randomElements([
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
            ], $this->faker->numberBetween(2, 6)),
            'metadata' => [
                'manufacturer' => $this->faker->randomElement(['Volvo', 'Mercedes', 'Scania', 'MAN', 'Isuzu']),
                'model' => $this->faker->word(),
                'year' => $this->faker->numberBetween(2015, 2024),
                'fuel_type' => $this->faker->randomElement(['Diesel', 'CNG', 'Electric']),
                'mileage' => $this->faker->numberBetween(50000, 500000),
                'last_service_date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Create an AC bus.
     */
    public function ac(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BusType::Ac,
        ]);
    }

    /**
     * Create a Non-AC bus.
     */
    public function nonAc(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BusType::NonAc,
        ]);
    }

    /**
     * Create a luxury bus.
     */
    public function luxury(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BusCategory::Luxury,
            'total_seats' => $this->faker->numberBetween(20, 40), // Luxury buses have fewer seats
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
        ]);
    }

    /**
     * Create a sleeper bus.
     */
    public function sleeper(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BusCategory::Sleeper,
            'total_seats' => $this->faker->numberBetween(24, 36), // Sleeper buses have berths
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
        ]);
    }

    /**
     * Create a standard bus.
     */
    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BusCategory::Economy,
            'total_seats' => $this->faker->numberBetween(40, 60), // Standard buses have more seats
            'amenities' => [
                'Comfortable Seats',
                'Reading Lights',
                'GPS Tracking',
                'CCTV Security',
            ],
        ]);
    }

    /**
     * Create an active bus.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive bus.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a bus for a specific operator.
     */
    public function forOperator(Operator $operator): static
    {
        return $this->state(fn (array $attributes) => [
            'operator_id' => $operator->id,
        ]);
    }
}
