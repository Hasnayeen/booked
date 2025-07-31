<?php

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
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('type');
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->json('booking_details')->comment('Flexible field for booking-specific data');
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
            $table->foreignId('bus_id')->nullable()->constrained()->onDelete('set null');
            $table->string('route_name');
            $table->string('origin_city');
            $table->string('destination_city');
            $table->time('departure_time'); // Start time for the journey
            $table->time('arrival_time'); // End time for the journey
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->json('off_days')->nullable(); // Days when route is not operational (e.g., ['sunday', 'monday'])
            $table->boolean('is_active')->default(true);
            $table->json('stops')->nullable(); // Intermediate stops
            $table->json('boarding_points')->nullable(); // Points where passengers can board
            $table->json('drop_off_points')->nullable(); // Points where passengers can drop off
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('operator_id');
            $table->index('bus_id');
            $table->index('origin_city');
            $table->index('destination_city');
            $table->index('departure_time');
            $table->index('is_active');
            $table->index(['operator_id', 'origin_city', 'destination_city']); // Common search pattern
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
        Schema::dropIfExists('buses');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('bookings');
    }
};
