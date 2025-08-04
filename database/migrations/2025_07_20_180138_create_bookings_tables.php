<?php

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->uuid('booking_reference')->unique();
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('total_fare');
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default(BookingStatus::Pending->value);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('operator_id');
            $table->index('type');
            $table->index('status');
            $table->index(['operator_id', 'type']);
        });

        // Rooms table (for hotel operators)
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->onDelete('cascade');
            $table->string('room_number');
            $table->string('type');
            $table->unsignedBigInteger('price_per_night');
            $table->integer('capacity');
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->json('amenities')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['operator_id', 'room_number']);
            $table->index('operator_id');
            $table->index('type');
        });

        // Buses table (for bus operators)
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->onDelete('cascade');
            $table->string('bus_number');
            $table->string('category');
            $table->string('type');
            $table->integer('total_seats');
            $table->string('license_plate')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('seat_config')->nullable()->comment('Configuration of seats (e.g., layout, seat numbers)');
            $table->json('amenities')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Ensure bus numbers are unique per operator
            $table->unique(['operator_id', 'bus_number']);
            $table->index('operator_id');
            $table->index('category');
            $table->index('type');
        });

        // Routes table (for bus operators)
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->onDelete('cascade');
            $table->string('route_name');
            $table->string('origin_city');
            $table->string('destination_city');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('stops')->nullable(); // Intermediate stops
            $table->json('boarding_points')->nullable(); // Points where passengers can board
            $table->json('drop_off_points')->nullable(); // Points where passengers can drop off
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('operator_id');
            $table->index('origin_city');
            $table->index('destination_city');
            $table->index('is_active');
            $table->index(['operator_id', 'origin_city', 'destination_city']); // Common search pattern
        });

        // Route schedules table (for bus route schedules)
        Schema::create('route_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained()->onDelete('cascade');
            $table->foreignId('bus_id')->constrained()->onDelete('cascade');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->json('off_days')->nullable(); // Days when this schedule is not operational
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('operator_id');
            $table->index('route_id');
            $table->index('bus_id');
            $table->index('departure_time');
            $table->index('is_active');
            $table->index(['route_id', 'departure_time']);
            $table->index(['route_id', 'bus_id']);
            $table->index(['operator_id', 'route_id']);
        });

        // Hotel bookings table (specific details for room bookings)
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights');
            $table->integer('guests');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->unsignedBigInteger('room_rate_per_night');
            $table->unsignedBigInteger('total_room_amount');
            $table->unsignedBigInteger('taxes')->default(0);
            $table->unsignedBigInteger('service_charges')->default(0);
            $table->text('special_requests')->nullable();
            $table->json('guest_details')->nullable()->comment('Names and details of all guests');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('room_id');
            $table->index('check_in_date');
            $table->index('check_out_date');
            $table->index(['check_in_date', 'check_out_date']);
        });

        // Bus bookings table (specific details for bus seat bookings)
        Schema::create('bus_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_schedule_id')->constrained()->onDelete('cascade');
            $table->date('travel_date');
            $table->json('seat_numbers')->comment('Array of booked seat numbers');
            $table->integer('passenger_count');
            $table->unsignedBigInteger('base_fare_per_seat');
            $table->unsignedBigInteger('total_base_fare');
            $table->unsignedBigInteger('taxes')->default(0);
            $table->unsignedBigInteger('service_charges')->default(0);
            $table->string('boarding_point')->nullable();
            $table->string('drop_off_point')->nullable();
            $table->time('boarding_time')->nullable();
            $table->time('drop_off_time')->nullable();
            $table->json('passenger_details')->comment('Names, ages, and details of all passengers');
            $table->text('special_requirements')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('route_schedule_id');
            $table->index('travel_date');
            $table->index(['route_schedule_id', 'travel_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_bookings');
        Schema::dropIfExists('hotel_bookings');
        Schema::dropIfExists('route_schedules');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('buses');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('bookings');
    }
};
