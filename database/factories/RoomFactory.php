<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Operator;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $roomTypes = ['standard', 'deluxe', 'suite', 'presidential', 'family', 'twin', 'single'];
        $roomType = fake()->randomElement($roomTypes);

        // Price varies by room type
        $priceRanges = [
            'standard' => [3000, 8000],     // $30-80
            'deluxe' => [6000, 15000],      // $60-150
            'suite' => [12000, 30000],      // $120-300
            'presidential' => [25000, 100000], // $250-1000
            'family' => [8000, 20000],      // $80-200
            'twin' => [4000, 10000],        // $40-100
            'single' => [2500, 6000],       // $25-60
        ];

        $capacity = match ($roomType) {
            'single' => 1,
            'twin' => 2,
            'standard' => fake()->numberBetween(1, 2),
            'deluxe' => fake()->numberBetween(2, 3),
            'family' => fake()->numberBetween(3, 6),
            'suite' => fake()->numberBetween(2, 4),
            'presidential' => fake()->numberBetween(2, 8),
            default => fake()->numberBetween(1, 4),
        };

        [$minPrice, $maxPrice] = $priceRanges[$roomType];

        return [
            'operator_id' => Operator::factory(),
            'room_number' => fake()->numberBetween(100, 99999),
            'type' => $roomType,
            'price_per_night' => fake()->numberBetween($minPrice, $maxPrice),
            'capacity' => $capacity,
            'description' => fake()->optional()->paragraph(),
            'is_available' => fake()->boolean(85), // 85% chance of being available
            'amenities' => fake()->optional()->randomElements([
                'wifi',
                'air_conditioning',
                'television',
                'mini_bar',
                'room_service',
                'balcony',
                'city_view',
                'ocean_view',
                'jacuzzi',
                'kitchenette',
                'safe',
                'iron',
                'hairdryer',
                'coffee_maker',
                'refrigerator',
            ], fake()->numberBetween(3, 8)),
            'metadata' => fake()->optional()->randomElement([
                null,
                [
                    'floor' => fake()->numberBetween(1, 20),
                    'wing' => fake()->randomElement(['east', 'west', 'north', 'south']),
                    'recently_renovated' => fake()->boolean(),
                    'smoking_allowed' => fake()->boolean(20), // 20% smoking rooms
                    'wheelchair_accessible' => fake()->boolean(30),
                ],
            ]),
        ];
    }

    /**
     * Create a luxury room.
     */
    public function luxury(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => fake()->randomElement(['suite', 'presidential']),
            'price_per_night' => fake()->numberBetween(20000, 80000),
            'capacity' => fake()->numberBetween(2, 6),
            'amenities' => [
                'wifi',
                'air_conditioning',
                'television',
                'mini_bar',
                'room_service',
                'balcony',
                'city_view',
                'jacuzzi',
                'kitchenette',
                'safe',
                'concierge_service',
                'butler_service',
                'premium_linens',
            ],
            'description' => 'Luxurious accommodations with premium amenities and exceptional service.',
        ]);
    }

    /**
     * Create a budget room.
     */
    public function budget(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => fake()->randomElement(['standard', 'single']),
            'price_per_night' => fake()->numberBetween(2000, 6000),
            'capacity' => fake()->numberBetween(1, 2),
            'amenities' => fake()->randomElements([
                'wifi',
                'air_conditioning',
                'television',
                'safe',
            ], fake()->numberBetween(2, 4)),
            'description' => 'Comfortable and affordable accommodation with essential amenities.',
        ]);
    }

    /**
     * Create a family room.
     */
    public function family(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'family',
            'price_per_night' => fake()->numberBetween(8000, 20000),
            'capacity' => fake()->numberBetween(4, 6),
            'amenities' => [
                'wifi',
                'air_conditioning',
                'television',
                'mini_bar',
                'kitchenette',
                'safe',
                'extra_bed',
                'children_amenities',
            ],
            'description' => 'Spacious room perfect for families with children, featuring additional space and family-friendly amenities.',
        ]);
    }

    /**
     * Create an unavailable room.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_available' => false,
            'metadata' => [
                'reason' => fake()->randomElement([
                    'maintenance',
                    'renovation',
                    'deep_cleaning',
                    'repair',
                    'reserved',
                ]),
                'expected_available_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            ],
        ]);
    }
}
