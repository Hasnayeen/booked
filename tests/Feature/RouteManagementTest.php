<?php

use App\Filament\Operator\Resources\Routes\Pages\CreateRoute;
use App\Filament\Operator\Resources\Routes\Pages\ListRoutes;
use App\Models\Bus;
use App\Models\Operator;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Route;
use App\Models\User;

use function Pest\Livewire\livewire;

describe('Route Management', function (): void {
    beforeEach(function (): void {
        $this->operator = Operator::factory()->create(['status' => 'approved']);
        $this->otherOperator = Operator::factory()->create(['status' => 'approved']);

        $this->adminRole = Role::firstOrCreate(['name' => 'Operator Admin']);
        $this->staffRole = Role::firstOrCreate(['name' => 'Operator Staff']);

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->operators()->attach($this->operator, ['role_id' => $this->adminRole->id]);

        $this->staffUser = User::factory()->create();
        $this->staffUser->operators()->attach($this->operator, ['role_id' => $this->staffRole->id]);

        // Create buses
        $this->bus = Bus::factory()->create(['operator_id' => $this->operator->id]);
        $this->otherBus = Bus::factory()->create(['operator_id' => $this->otherOperator->id]);
        $this->otherRoute = Route::factory()->create([
            'operator_id' => $this->otherOperator->id,
            'bus_id' => $this->otherBus->id,
        ]);

        // Set current tenant
        filament()->setCurrentPanel('operator');
        $this->actingAs($this->adminUser);
        filament()->setTenant($this->operator);
    });

    describe('Route Creation', function (): void {
        it('can create route through filament admin panel', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'bus_id' => $this->bus->id,
                    'route_name' => 'Downtown Express',
                    'origin_city' => 'New York',
                    'destination_city' => 'Boston',
                    'departure_time' => '08:00',
                    'arrival_time' => '12:30',
                    'distance_km' => 350.5,
                    'is_active' => true,
                    'off_days' => [
                        ['type' => 'day', 'value' => 'sunday'],
                    ],
                    'stops' => [
                        ['stop' => 'Central Station', 'time' => '08:30'],
                        ['stop' => 'Airport Terminal', 'time' => '09:00'],
                    ],
                    'boarding_points' => [
                        ['point' => 'Platform A', 'time' => '07:45'],
                    ],
                    'drop_off_points' => [
                        ['point' => 'Terminal B', 'time' => '12:45'],
                    ],
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('routes', [
                'route_name' => 'Downtown Express',
                'origin_city' => 'New York',
                'destination_city' => 'Boston',
                'departure_time' => '08:00',
                'arrival_time' => '12:30',
                'distance_km' => 350.5,
                'is_active' => true,
            ]);

            $route = Route::where('route_name', 'Downtown Express')->first();
            expect($route->estimated_duration)->toBe('4h 30m');
            expect($route->off_days)->toBe([['type' => 'day', 'value' => 'sunday']]);
            expect($route->stops)->toBe([['stop' => 'Central Station', 'time' => '08:30'], ['stop' => 'Airport Terminal', 'time' => '09:00']]);
            expect($route->boarding_points)->toBe([['point' => 'Platform A', 'time' => '07:45']]);
            expect($route->drop_off_points)->toBe([['point' => 'Terminal B', 'time' => '12:45']]);
        });

        it('can create route with specific date off days', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'bus_id' => $this->bus->id,
                    'route_name' => 'Holiday Special',
                    'origin_city' => 'New York',
                    'destination_city' => 'Boston',
                    'departure_time' => '08:00',
                    'arrival_time' => '12:30',
                    'is_active' => true,
                    'off_days' => [
                        ['type' => 'date', 'value' => '2025-12-25'], // Christmas
                        ['type' => 'date', 'value' => '2025-01-01'], // New Year's Day
                        ['type' => 'day', 'value' => 'sunday'], // Every Sunday
                    ],
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('route_name', 'Holiday Special')->first();
            expect($route->off_days)->toBe([
                ['type' => 'date', 'value' => '2025-12-25'],
                ['type' => 'date', 'value' => '2025-01-01'],
                ['type' => 'day', 'value' => 'sunday'],
            ]);
        });

        it('can create route with mixed off days (dates and weekdays)', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'bus_id' => $this->bus->id,
                    'route_name' => 'Flexible Route',
                    'origin_city' => 'Chicago',
                    'destination_city' => 'Detroit',
                    'departure_time' => '10:00',
                    'arrival_time' => '15:00',
                    'is_active' => true,
                    'off_days' => [
                        ['type' => 'day', 'value' => 'saturday'],
                        ['type' => 'day', 'value' => 'sunday'],
                        ['type' => 'date', 'value' => '2025-07-04'], // Independence Day
                        ['type' => 'date', 'value' => '2025-11-28'], // Thanksgiving
                    ],
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('route_name', 'Flexible Route')->first();
            expect($route->off_days)->toHaveCount(4);
            expect($route->off_days[2])->toBe(['type' => 'date', 'value' => '2025-07-04']);
            expect($route->off_days[3])->toBe(['type' => 'date', 'value' => '2025-11-28']);
        });

        it('automatically calculates estimated duration from departure and arrival times', function (): void {
            $this->actingAs($this->adminUser);

            // Test normal same-day route
            livewire(CreateRoute::class)
                ->fillForm([
                    'bus_id' => $this->bus->id,
                    'route_name' => 'Morning Route',
                    'origin_city' => 'New York',
                    'destination_city' => 'Boston',
                    'departure_time' => '09:00',
                    'arrival_time' => '13:45',
                    'is_active' => true,
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('route_name', 'Morning Route')->first();
            expect($route->estimated_duration)->toBe('4h 45m');
        });

        it('correctly calculates estimated duration for overnight routes', function (): void {
            $this->actingAs($this->adminUser);

            // Test overnight route (departure 23:30, arrival 03:15 next day)
            livewire(CreateRoute::class)
                ->fillForm([
                    'bus_id' => $this->bus->id,
                    'route_name' => 'Overnight Express',
                    'origin_city' => 'Los Angeles',
                    'destination_city' => 'San Francisco',
                    'departure_time' => '23:30',
                    'arrival_time' => '03:15',
                    'is_active' => true,
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('route_name', 'Overnight Express')->first();
            expect($route->estimated_duration)->toBe('3h 45m');
        });

        it('validates required fields when creating route', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'bus_id' => null,
                    'route_name' => '',
                    'origin_city' => '',
                    'destination_city' => '',
                    'departure_time' => '',
                    'arrival_time' => '',
                ])
                ->call('create')
                ->assertHasFormErrors([
                    'route_name' => 'required',
                    'origin_city' => 'required',
                    'destination_city' => 'required',
                    'departure_time' => 'required',
                    'arrival_time' => 'required',
                ]);
        });
    });

    describe('Route Listing', function (): void {
        it('can list routes through filament admin panel', function (): void {
            $this->actingAs($this->adminUser);

            Route::factory()->count(3)->create([
                'operator_id' => $this->operator->id,
                'bus_id' => $this->bus->id,
            ]);

            livewire(ListRoutes::class)
                ->assertCanSeeTableRecords(Route::where('operator_id', $this->operator->id)->get());
        });

        it('cannot see other operators routes', function (): void {
            $this->actingAs($this->adminUser);

            $response = $this->get("/operator/{$this->operator->id}/routes");

            $response->assertStatus(200)
                ->assertSee($this->bus->route_name)
                ->assertDontSee($this->otherRoute->route_name);
        });
    });

    describe('Route Authorization', function (): void {
        it('restricts access for users without proper permissions', function (): void {
            // Remove manage_routes permission from staff role for this test
            $manageRoutesPermission = Permission::where('name', 'manage_routes')->first();
            $this->staffRole->permissions()->detach($manageRoutesPermission->id);

            $user = User::factory()->create();
            $this->operator->users()->attach($user, ['role_id' => $this->staffRole->id]);

            $this->actingAs($user);
            filament()->setTenant($this->operator);

            livewire(CreateRoute::class)
                ->assertForbidden();
        });
    });
});
