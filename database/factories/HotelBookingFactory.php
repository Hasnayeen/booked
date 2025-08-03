<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\HotelBooking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HotelBooking>
 */
class HotelBookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = HotelBooking::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $checkInDate = fake()->dateTimeBetween('now', '+30 days');
        $checkOutDate = fake()->dateTimeBetween($checkInDate, $checkInDate->format('Y-m-d') . ' +7 days');
        $nights = $checkInDate->diff($checkOutDate)->days;

        $adults = fake()->numberBetween(1, 4);
        $children = fake()->numberBetween(0, 2);
        $guests = $adults + $children;

        $roomRatePerNight = fake()->numberBetween(5000, 50000); // $50 - $500 per night in cents
        $totalRoomAmount = $roomRatePerNight * $nights;
        $taxes = (int) ($totalRoomAmount * 0.1); // 10% tax
        $serviceCharges = fake()->numberBetween(500, 2000); // $5 - $20 service charges

        return [
            'booking_id' => Booking::factory(),
            'room_id' => Room::factory(),
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'nights' => $nights,
            'guests' => $guests,
            'adults' => $adults,
            'children' => $children,
            'room_rate_per_night' => $roomRatePerNight,
            'total_room_amount' => $totalRoomAmount,
            'taxes' => $taxes,
            'service_charges' => $serviceCharges,
            'special_requests' => fake()->optional()->paragraph(),
            'guest_details' => $this->generateGuestDetails($adults, $children),
            'metadata' => fake()->optional()->randomElement([
                null,
                [
                    'early_checkin' => fake()->boolean(),
                    'late_checkout' => fake()->boolean(),
                    'room_preferences' => fake()->randomElements(['non-smoking', 'high-floor', 'city-view', 'quiet'], fake()->numberBetween(0, 2)),
                ],
            ]),
        ];
    }

    /**
     * Generate guest details array.
     */
    private function generateGuestDetails(int $adults, int $children): array
    {
        $guests = [];

        // Add adults
        for ($i = 0; $i < $adults; $i++) {
            $guests[] = [
                'name' => fake()->name(),
                'type' => 'adult',
                'age' => fake()->numberBetween(18, 65),
                'id_type' => fake()->randomElement(['passport', 'national_id', 'driving_license']),
                'id_number' => fake()->bothify('???#######'),
            ];
        }

        // Add children
        for ($i = 0; $i < $children; $i++) {
            $guests[] = [
                'name' => fake()->firstName() . ' ' . fake()->lastName(),
                'type' => 'child',
                'age' => fake()->numberBetween(2, 17),
            ];
        }

        return $guests;
    }

    /**
     * Create a hotel booking for a weekend stay.
     */
    public function weekend(): static
    {
        return $this->state(function (array $attributes): array {
            $friday = fake()->dateTimeBetween('now', '+30 days')->modify('next friday');
            $sunday = clone $friday;
            $sunday->modify('+2 days');

            $nights = 2;
            $roomRatePerNight = fake()->numberBetween(8000, 25000); // Higher weekend rates
            $totalRoomAmount = $roomRatePerNight * $nights;

            return [
                'check_in_date' => $friday,
                'check_out_date' => $sunday,
                'nights' => $nights,
                'room_rate_per_night' => $roomRatePerNight,
                'total_room_amount' => $totalRoomAmount,
                'taxes' => (int) ($totalRoomAmount * 0.1),
            ];
        });
    }

    /**
     * Create a hotel booking for a long stay.
     */
    public function longStay(): static
    {
        return $this->state(function (array $attributes): array {
            $checkInDate = fake()->dateTimeBetween('now', '+14 days');
            $checkOutDate = fake()->dateTimeBetween($checkInDate->format('Y-m-d') . ' +7 days', $checkInDate->format('Y-m-d') . ' +30 days');
            $nights = $checkInDate->diff($checkOutDate)->days;

            $roomRatePerNight = fake()->numberBetween(3000, 15000); // Discounted long stay rates
            $totalRoomAmount = $roomRatePerNight * $nights;

            return [
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'nights' => $nights,
                'room_rate_per_night' => $roomRatePerNight,
                'total_room_amount' => $totalRoomAmount,
                'taxes' => (int) ($totalRoomAmount * 0.12), // Higher tax for long stays
                'service_charges' => fake()->numberBetween(1000, 3000),
            ];
        });
    }
}
