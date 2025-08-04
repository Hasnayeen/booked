<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Bus;
use App\Models\BusBooking;
use App\Models\HotelBooking;
use App\Models\Operator;
use App\Models\Room;
use App\Models\Route;
use App\Models\RouteSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding bookings...');

        // Reset Faker's unique constraint to avoid duplicate email errors
        fake()->unique(true);

        // Get or create a limited set of operators
        $hotelOperators = Operator::where('type', 'hotel')->limit(5)->get();
        $busOperators = Operator::where('type', 'bus')->limit(5)->get();

        if ($hotelOperators->isEmpty()) {
            $this->command->info('Creating hotel operators...');
            $hotelOperators = collect([
                Operator::factory()->hotel()->approved()->create(),
                Operator::factory()->hotel()->approved()->create(),
            ]);
        }

        if ($busOperators->isEmpty()) {
            $this->command->info('Creating bus operators...');
            $busOperators = collect([
                Operator::factory()->bus()->approved()->create(),
                Operator::factory()->bus()->approved()->create(),
            ]);
        }

        $hotelOperators->merge($busOperators);
        $users = User::all();

        // Create rooms and routes for these specific operators
        $this->ensureRoomsExist($hotelOperators);
        $this->ensureRoutesExist($busOperators);

        // Get the created rooms and routes
        $rooms = Room::whereIn('operator_id', $hotelOperators->pluck('id'))->get();
        Route::whereIn('operator_id', $busOperators->pluck('id'))->get();

        // Create hotel bookings
        $this->createHotelBookings($hotelOperators, $users, $rooms);

        // Create bus bookings
        $this->createBusBookings($busOperators, $users);

        $this->command->info('Bookings seeded successfully!');
        $this->command->info('Total bookings created: ' . Booking::count());
        $this->command->info('Hotel bookings: ' . HotelBooking::count());
        $this->command->info('Bus bookings: ' . BusBooking::count());
    }

    /**
     * Ensure rooms exist for hotel bookings.
     */
    private function ensureRoomsExist($hotelOperators): void
    {
        foreach ($hotelOperators as $operator) {
            $roomCount = Room::where('operator_id', $operator->id)->count();

            if ($roomCount < 5) {
                $this->command->info("Creating rooms for hotel operator: {$operator->name}");
                // Create different types of rooms for this specific operator
                Room::factory()->count(3)->for($operator)->create();
                Room::factory()->luxury()->count(2)->for($operator)->create();
                Room::factory()->budget()->count(2)->for($operator)->create();
                Room::factory()->family()->count(1)->for($operator)->create();
                Room::factory()->unavailable()->count(1)->for($operator)->create();
            }
        }
    }

    /**
     * Ensure routes exist for bus bookings.
     */
    private function ensureRoutesExist($busOperators): void
    {
        foreach ($busOperators as $operator) {
            $routeCount = Route::where('operator_id', $operator->id)->count();

            if ($routeCount < 3) {
                $this->command->info("Creating routes for bus operator: {$operator->name}");
                $routes = Route::factory()->count(5)->for($operator)->create();

                // Create schedules for each route
                $this->createSchedulesForRoutes($routes, $operator);
            }
        }
    }

    /**
     * Create multiple schedules for each route.
     */
    private function createSchedulesForRoutes($routes, $operator): void
    {
        $buses = Bus::where('operator_id', $operator->id)->get();

        // If no buses exist for this operator, create some
        if ($buses->isEmpty()) {
            $this->command->info("Creating buses for operator: {$operator->name}");
            $buses = Bus::factory()->count(3)->for($operator)->create();
        }

        foreach ($routes as $route) {
            // Create 2-4 schedules per route with different times
            $scheduleCount = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $scheduleCount; $i++) {
                $bus = $buses->random();

                RouteSchedule::factory()->create([
                    'operator_id' => $operator->id,
                    'route_id' => $route->id,
                    'bus_id' => $bus->id,
                ]);
            }
        }
    }

    /**
     * Create hotel bookings with realistic scenarios.
     */
    private function createHotelBookings($operators, $users, $rooms): void
    {
        $this->command->info('Creating hotel bookings...');

        // Confirmed hotel bookings (60%)
        $confirmedCount = 15;
        for ($i = 0; $i < $confirmedCount; $i++) {
            $operator = $operators->random();
            $room = $rooms->where('operator_id', $operator->id)->where('is_available', true)->random();
            $user = $users->random();

            // Create main booking
            $booking = Booking::factory()->hotel()->confirmed()->create([
                'operator_id' => $operator->id,
                'user_id' => fake()->boolean(70) ? $user->id : null,
                'type' => 'hotel',
            ]);

            // Create hotel booking details
            $checkInDate = fake()->dateTimeBetween('now', '+30 days');
            $checkOutDate = fake()->dateTimeBetween($checkInDate, $checkInDate->format('Y-m-d') . ' +7 days');
            $nights = $checkInDate->diff($checkOutDate)->days;

            HotelBooking::factory()->create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'nights' => $nights,
                'room_rate_per_night' => $room->price_per_night,
                'total_room_amount' => $room->price_per_night * $nights,
            ]);

            // Update main booking total
            $hotelBooking = $booking->hotelBooking;
            $totalAmount = ($hotelBooking->total_room_amount + $hotelBooking->taxes + $hotelBooking->service_charges) / 100;
            $booking->update(['total_fare' => $totalAmount]);
        }

        // Pending hotel bookings (20%)
        $pendingCount = 5;
        for ($i = 0; $i < $pendingCount; $i++) {
            $operator = $operators->random();
            $room = $rooms->where('operator_id', $operator->id)->where('is_available', true)->random();

            $booking = Booking::factory()->hotel()->pending()->create([
                'operator_id' => $operator->id,
                'user_id' => fake()->boolean(50) ? $users->random()->id : null,
                'type' => 'hotel',
            ]);

            HotelBooking::factory()->create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'room_rate_per_night' => $room->price_per_night,
            ]);
        }

        // Cancelled hotel bookings (15%)
        $cancelledCount = 4;
        for ($i = 0; $i < $cancelledCount; $i++) {
            $operator = $operators->random();
            $room = $rooms->where('operator_id', $operator->id)->random();

            $booking = Booking::factory()->hotel()->cancelled()->create([
                'operator_id' => $operator->id,
                'user_id' => fake()->boolean(80) ? $users->random()->id : null,
                'type' => 'hotel',
            ]);

            HotelBooking::factory()->create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'room_rate_per_night' => $room->price_per_night,
            ]);
        }

        // Weekend hotel bookings
        $weekendCount = 3;
        for ($i = 0; $i < $weekendCount; $i++) {
            $operator = $operators->random();
            $room = $rooms->where('operator_id', $operator->id)->where('is_available', true)->random();

            $booking = Booking::factory()->hotel()->confirmed()->create([
                'operator_id' => $operator->id,
                'user_id' => $users->random()->id,
                'type' => 'hotel',
            ]);

            HotelBooking::factory()->weekend()->create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'room_rate_per_night' => $room->price_per_night * 1.2, // Weekend premium
            ]);
        }

        // Long stay bookings
        $longStayCount = 2;
        for ($i = 0; $i < $longStayCount; $i++) {
            $operator = $operators->random();
            $room = $rooms->where('operator_id', $operator->id)->where('is_available', true)->random();

            $booking = Booking::factory()->hotel()->confirmed()->create([
                'operator_id' => $operator->id,
                'user_id' => $users->random()->id,
                'type' => 'hotel',
            ]);

            HotelBooking::factory()->longStay()->create([
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'room_rate_per_night' => $room->price_per_night * 0.8, // Long stay discount
            ]);
        }
    }

    /**
     * Create bus bookings with realistic scenarios.
     */
    private function createBusBookings($operators, $users): void
    {
        $this->command->info('Creating bus bookings...');

        // Get all route schedules for bus operators
        $routeSchedules = RouteSchedule::whereHas('route', function ($query) use ($operators): void {
            $query->whereIn('operator_id', $operators->pluck('id'))
                ->where('is_active', true);
        })->where('is_active', true)->get();

        if ($routeSchedules->isEmpty()) {
            $this->command->warn('No route schedules available for bus bookings');

            return;
        }

        // Confirmed bus bookings (65%)
        $confirmedCount = 20;
        for ($i = 0; $i < $confirmedCount; $i++) {
            $routeSchedule = $routeSchedules->random();
            $operator = $routeSchedule->route->operator;
            $user = $users->random();

            $booking = Booking::factory()->bus()->confirmed()->create([
                'operator_id' => $operator->id,
                'user_id' => fake()->boolean(75) ? $user->id : null,
                'type' => 'bus',
            ]);

            $busBooking = BusBooking::factory()->create([
                'booking_id' => $booking->id,
                'route_schedule_id' => $routeSchedule->id,
            ]);

            // Update main booking total
            $totalAmount = ($busBooking->total_base_fare + $busBooking->taxes + $busBooking->service_charges) / 100;
            $booking->update(['total_fare' => $totalAmount]);
        }

        // Pending bus bookings (20%)
        $pendingCount = 6;
        for ($i = 0; $i < $pendingCount; $i++) {
            $routeSchedule = $routeSchedules->random();
            $operator = $routeSchedule->route->operator;

            $booking = Booking::factory()->bus()->pending()->create([
                'operator_id' => $operator->id,
                'user_id' => fake()->boolean(60) ? $users->random()->id : null,
                'type' => 'bus',
            ]);

            BusBooking::factory()->create([
                'booking_id' => $booking->id,
                'route_schedule_id' => $routeSchedule->id,
            ]);
        }

        // Cancelled bus bookings (10%)
        $cancelledCount = 3;
        for ($i = 0; $i < $cancelledCount; $i++) {
            $routeSchedule = $routeSchedules->random();
            $operator = $routeSchedule->route->operator;

            $booking = Booking::factory()->bus()->cancelled()->create([
                'operator_id' => $operator->id,
                'user_id' => fake()->boolean(85) ? $users->random()->id : null,
                'type' => 'bus',
            ]);

            BusBooking::factory()->create([
                'booking_id' => $booking->id,
                'route_schedule_id' => $routeSchedule->id,
            ]);
        }

        // Single passenger bookings
        $singleCount = 8;
        for ($i = 0; $i < $singleCount; $i++) {
            $routeSchedule = $routeSchedules->random();
            $operator = $routeSchedule->route->operator;

            $booking = Booking::factory()->bus()->confirmed()->create([
                'operator_id' => $operator->id,
                'user_id' => $users->random()->id,
                'type' => 'bus',
            ]);

            BusBooking::factory()->singlePassenger()->create([
                'booking_id' => $booking->id,
                'route_schedule_id' => $routeSchedule->id,
            ]);
        }

        // Group bookings
        $groupCount = 3;
        for ($i = 0; $i < $groupCount; $i++) {
            $routeSchedule = $routeSchedules->random();
            $operator = $routeSchedule->route->operator;

            $booking = Booking::factory()->bus()->confirmed()->create([
                'operator_id' => $operator->id,
                'user_id' => $users->random()->id,
                'type' => 'bus',
            ]);

            BusBooking::factory()->groupBooking()->create([
                'booking_id' => $booking->id,
                'route_schedule_id' => $routeSchedule->id,
            ]);
        }

        // Premium service bookings
        $premiumCount = 2;
        for ($i = 0; $i < $premiumCount; $i++) {
            $routeSchedule = $routeSchedules->random();
            $operator = $routeSchedule->route->operator;

            $booking = Booking::factory()->bus()->confirmed()->create([
                'operator_id' => $operator->id,
                'user_id' => $users->random()->id,
                'type' => 'bus',
                'total_fare' => fake()->randomFloat(2, 80.00, 250.00), // Higher premium amounts
            ]);

            BusBooking::factory()->premiumService()->create([
                'booking_id' => $booking->id,
                'route_schedule_id' => $routeSchedule->id,
            ]);
        }
    }
}
