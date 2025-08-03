<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BusBooking;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusBooking>
 */
class BusBookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = BusBooking::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $passengerCount = fake()->numberBetween(1, 4);
        $seatNumbers = $this->generateSeatNumbers($passengerCount);
        
        $baseFarePerSeat = fake()->numberBetween(1000, 15000); // $10 - $150 per seat in cents
        $totalBaseFare = $baseFarePerSeat * $passengerCount;
        $taxes = (int) ($totalBaseFare * 0.05); // 5% tax
        $serviceCharges = fake()->numberBetween(200, 1000); // $2 - $10 service charges

        $boardingPoints = ['Main Terminal', 'City Center', 'Airport', 'Mall Junction', 'Railway Station'];
        $dropOffPoints = ['Central Bus Station', 'Downtown', 'Shopping District', 'University', 'Hotel District'];

        return [
            'booking_id' => Booking::factory(),
            'route_id' => Route::factory(),
            'travel_date' => fake()->dateTimeBetween('now', '+60 days'),
            'seat_numbers' => $seatNumbers,
            'passenger_count' => $passengerCount,
            'base_fare_per_seat' => $baseFarePerSeat,
            'total_base_fare' => $totalBaseFare,
            'taxes' => $taxes,
            'service_charges' => $serviceCharges,
            'boarding_point' => fake()->randomElement($boardingPoints),
            'drop_off_point' => fake()->randomElement($dropOffPoints),
            'boarding_time' => fake()->time('H:i'),
            'drop_off_time' => fake()->time('H:i'),
            'passenger_details' => $this->generatePassengerDetails($passengerCount),
            'special_requirements' => fake()->optional()->randomElement([
                null,
                'Wheelchair accessibility',
                'Extra legroom',
                'Window seat preference',
                'Aisle seat preference',
                'No air conditioning',
                'Vegetarian meal',
            ]),
            'metadata' => fake()->optional()->randomElement([
                null,
                [
                    'booking_source' => fake()->randomElement(['web', 'mobile_app', 'agent', 'phone']),
                    'payment_method' => fake()->randomElement(['card', 'wallet', 'bank_transfer', 'cash']),
                    'discount_applied' => fake()->boolean(),
                    'insurance_opted' => fake()->boolean(),
                ],
            ]),
        ];
    }

    /**
     * Generate seat numbers array.
     */
    private function generateSeatNumbers(int $count): array
    {
        $seatNumbers = [];
        $usedSeats = [];
        
        for ($i = 0; $i < $count; $i++) {
            do {
                $seatNumber = fake()->numberBetween(1, 45);
            } while (in_array($seatNumber, $usedSeats));
            
            $usedSeats[] = $seatNumber;
            $seatNumbers[] = $seatNumber;
        }
        
        sort($seatNumbers);
        return $seatNumbers;
    }

    /**
     * Generate passenger details array.
     */
    private function generatePassengerDetails(int $count): array
    {
        $passengers = [];
        
        for ($i = 0; $i < $count; $i++) {
            $passengers[] = [
                'name' => fake()->name(),
                'age' => fake()->numberBetween(5, 80),
                'gender' => fake()->randomElement(['male', 'female', 'other']),
                'id_type' => fake()->randomElement(['passport', 'national_id', 'driving_license', 'voter_id']),
                'id_number' => fake()->bothify('???#######'),
                'phone' => fake()->optional()->phoneNumber(),
                'emergency_contact' => fake()->optional()->name(),
                'emergency_phone' => fake()->optional()->phoneNumber(),
            ];
        }
        
        return $passengers;
    }

    /**
     * Create a bus booking for a single passenger.
     */
    public function singlePassenger(): static
    {
        return $this->state(function (array $attributes) {
            $baseFarePerSeat = fake()->numberBetween(500, 8000);
            
            return [
                'passenger_count' => 1,
                'seat_numbers' => [fake()->numberBetween(1, 45)],
                'base_fare_per_seat' => $baseFarePerSeat,
                'total_base_fare' => $baseFarePerSeat,
                'passenger_details' => $this->generatePassengerDetails(1),
            ];
        });
    }

    /**
     * Create a bus booking for a group.
     */
    public function groupBooking(): static
    {
        return $this->state(function (array $attributes) {
            $passengerCount = fake()->numberBetween(4, 8);
            $baseFarePerSeat = fake()->numberBetween(2000, 12000);
            $totalBaseFare = $baseFarePerSeat * $passengerCount;
            
            return [
                'passenger_count' => $passengerCount,
                'seat_numbers' => $this->generateSeatNumbers($passengerCount),
                'base_fare_per_seat' => $baseFarePerSeat,
                'total_base_fare' => $totalBaseFare,
                'taxes' => (int) ($totalBaseFare * 0.05),
                'service_charges' => fake()->numberBetween(500, 2000), // Higher service charges for groups
                'passenger_details' => $this->generatePassengerDetails($passengerCount),
            ];
        });
    }

    /**
     * Create a bus booking for premium/luxury service.
     */
    public function premiumService(): static
    {
        return $this->state(function (array $attributes) {
            $passengerCount = fake()->numberBetween(1, 2);
            $baseFarePerSeat = fake()->numberBetween(8000, 25000); // Higher premium fares
            $totalBaseFare = $baseFarePerSeat * $passengerCount;
            
            return [
                'passenger_count' => $passengerCount,
                'base_fare_per_seat' => $baseFarePerSeat,
                'total_base_fare' => $totalBaseFare,
                'taxes' => (int) ($totalBaseFare * 0.08), // Higher tax for premium
                'service_charges' => fake()->numberBetween(1000, 3000),
                'special_requirements' => fake()->randomElement([
                    'Premium seating',
                    'Complimentary meals',
                    'Extra baggage allowance',
                    'Priority boarding',
                ]),
            ];
        });
    }
}
