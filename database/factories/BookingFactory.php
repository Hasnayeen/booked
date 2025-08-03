<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $bookingTypes = ['hotel', 'bus'];
        $type = fake()->randomElement($bookingTypes);
        
        // Generate amounts based on booking type
        $totalAmount = match($type) {
            'hotel' => fake()->randomFloat(2, 50.00, 1000.00), // $50 - $1000 for hotels
            'bus' => fake()->randomFloat(2, 10.00, 200.00),   // $10 - $200 for bus
            default => fake()->randomFloat(2, 20.00, 500.00),
        };

        return [
            'operator_id' => Operator::factory(),
            'user_id' => fake()->boolean(70) ? User::factory() : null, // 70% have associated user
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->optional()->phoneNumber(),
            'type' => $type,
            'total_fare' => $totalAmount,
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP', 'CAD', 'AUD']),
            'status' => fake()->randomElement(BookingStatus::cases())->value,
            'metadata' => fake()->optional()->randomElement([
                null,
                [
                    'source' => fake()->randomElement(['web', 'mobile', 'agent', 'phone']),
                    'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'referrer' => fake()->optional()->url(),
                    'utm_source' => fake()->optional()->word(),
                    'utm_campaign' => fake()->optional()->word(),
                ],
            ]),
        ];
    }

    /**
     * Generate booking details based on type.
     */
    private function generateBookingDetails(string $type): array
    {
        return match($type) {
            'hotel' => [
                'check_in' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
                'check_out' => fake()->dateTimeBetween('+1 day', '+37 days')->format('Y-m-d'),
                'guests' => fake()->numberBetween(1, 4),
                'rooms' => fake()->numberBetween(1, 2),
            ],
            'bus' => [
                'travel_date' => fake()->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
                'passengers' => fake()->numberBetween(1, 4),
                'departure_city' => fake()->city(),
                'arrival_city' => fake()->city(),
            ],
            default => [],
        };
    }

    /**
     * Create a confirmed booking.
     */
    public function confirmed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => BookingStatus::Confirmed->value,
                'metadata' => array_merge($attributes['metadata'] ?? [], [
                    'confirmed_at' => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d H:i:s'),
                    'confirmation_method' => fake()->randomElement(['auto', 'manual', 'payment']),
                ]),
            ];
        });
    }

    /**
     * Create a cancelled booking.
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => BookingStatus::Cancelled->value,
                'metadata' => array_merge($attributes['metadata'] ?? [], [
                    'cancelled_at' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
                    'cancellation_reason' => fake()->randomElement([
                        'customer_request',
                        'payment_failed',
                        'operator_unavailable',
                        'weather',
                        'technical_issue',
                    ]),
                    'refund_amount' => fake()->randomFloat(2, 0, $attributes['total_fare'] ?? 100),
                ]),
            ];
        });
    }

    /**
     * Create a pending booking.
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => BookingStatus::Pending->value,
                'metadata' => array_merge($attributes['metadata'] ?? [], [
                    'pending_reason' => fake()->randomElement([
                        'payment_processing',
                        'operator_approval',
                        'document_verification',
                        'availability_check',
                    ]),
                ]),
            ];
        });
    }

    /**
     * Create a hotel booking.
     */
    public function hotel(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'hotel',
                'total_fare' => fake()->randomFloat(2, 80.00, 800.00),
                // 'booking_details' => [
                //     'check_in' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
                //     'check_out' => fake()->dateTimeBetween('+1 day', '+37 days')->format('Y-m-d'),
                //     'guests' => fake()->numberBetween(1, 6),
                //     'rooms' => fake()->numberBetween(1, 3),
                //     'room_type' => fake()->randomElement(['standard', 'deluxe', 'suite']),
                // ],
            ];
        });
    }

    /**
     * Create a bus booking.
     */
    public function bus(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'bus',
                'total_fare' => fake()->randomFloat(2, 15.00, 150.00),
                // 'booking_details' => [
                //     'travel_date' => fake()->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
                //     'passengers' => fake()->numberBetween(1, 6),
                //     'departure_city' => fake()->city(),
                //     'arrival_city' => fake()->city(),
                //     'departure_time' => fake()->time('H:i'),
                //     'service_type' => fake()->randomElement(['standard', 'premium', 'luxury']),
                // ],
            ];
        });
    }

    /**
     * Create a recent booking (within last 7 days).
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
                'updated_at' => fake()->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }
}
