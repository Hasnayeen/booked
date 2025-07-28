<?php

namespace Database\Factories;

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Models\Operator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Operator>
 */
class OperatorFactory extends Factory
{
    protected $model = Operator::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(OperatorType::cases());

        return [
            'name' => $type === OperatorType::Hotel
                ? fake()->company() . ' Hotel'
                : fake()->company() . ' Bus Lines',
            'type' => $type,
            'status' => fake()->randomElement(OperatorStatus::cases()),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'description' => fake()->paragraph(),
            'metadata' => [
                'website' => fake()->url(),
                'founded' => fake()->year(),
                'rating' => fake()->randomFloat(1, 3.0, 5.0),
                'notifications' => [
                    'email' => fake()->boolean(),
                    'sms' => fake()->boolean(),
                ],
                'booking_settings' => [
                    'advance_booking_days' => fake()->numberBetween(1, 365),
                    'cancellation_policy' => fake()->randomElement(['flexible', 'moderate', 'strict']),
                ],
            ],
        ];
    }

    /**
     * Create a hotel operator.
     */
    public function hotel(): static
    {
        return $this->state([
            'type' => OperatorType::Hotel,
            'name' => fake()->company() . ' Hotel',
        ]);
    }

    /**
     * Create a bus operator.
     */
    public function bus(): static
    {
        return $this->state([
            'type' => OperatorType::Bus,
            'name' => fake()->company() . ' Bus Lines',
        ]);
    }

    /**
     * Create an active operator.
     */
    public function approved(): static
    {
        return $this->state([
            'status' => OperatorStatus::Approved,
        ]);
    }

    /**
     * Create a rejected operator.
     */
    public function rejected(): static
    {
        return $this->state([
            'status' => OperatorStatus::Rejected,
        ]);
    }

    /**
     * Create a suspended operator.
     */
    public function suspended(): static
    {
        return $this->state([
            'status' => OperatorStatus::Suspended,
        ]);
    }

    /**
     * Create a pending operator.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => OperatorStatus::Pending,
        ]);
    }
}
