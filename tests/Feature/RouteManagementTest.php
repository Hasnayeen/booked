<?php

declare(strict_types=1);

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
                    'route_name' => 'Downtown Express',
                    'origin_city' => 'New York',
                    'destination_city' => 'Boston',
                    'distance_km' => 350.5,
                    'is_active' => true,
                    'stops' => [
                        ['stop' => 'Central Station'],
                        ['stop' => 'Airport Terminal'],
                    ],
                    'boarding_points' => [
                        ['point' => 'Platform A'],
                    ],
                    'drop_off_points' => [
                        ['point' => 'Terminal B'],
                    ],
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('routes', [
                'route_name' => 'Downtown Express',
                'origin_city' => 'New York',
                'destination_city' => 'Boston',
                'distance_km' => 350.5,
                'is_active' => true,
            ]);

            $route = Route::where('route_name', 'Downtown Express')->first();
            expect($route->stops)->toBe([['stop' => 'Central Station'], ['stop' => 'Airport Terminal']]);
            expect($route->boarding_points)->toBe([['point' => 'Platform A']]);
            expect($route->drop_off_points)->toBe([['point' => 'Terminal B']]);
        });

        it('can create route with metadata', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'route_name' => 'Holiday Special',
                    'origin_city' => 'New York',
                    'destination_city' => 'Boston',
                    'is_active' => true,
                    'metadata' => [
                        'wifi_available' => 'true',
                        'refreshments' => 'false',
                        'route_type' => 'express',
                    ],
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('route_name', 'Holiday Special')->first();
            expect($route->metadata)->toBe([
                'wifi_available' => 'true',
                'refreshments' => 'false',
                'route_type' => 'express',
            ]);
        });

        it('can create route with multiple stops and points', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'route_name' => 'Flexible Route',
                    'origin_city' => 'Chicago',
                    'destination_city' => 'Detroit',
                    'is_active' => true,
                    'stops' => [
                        ['stop' => 'Central Station'],
                        ['stop' => 'Airport Terminal'],
                        ['stop' => 'Shopping Mall'],
                    ],
                    'boarding_points' => [
                        ['point' => 'Platform A'],
                        ['point' => 'Gate 5'],
                    ],
                    'drop_off_points' => [
                        ['point' => 'Terminal B'],
                        ['point' => 'Station Exit'],
                    ],
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('route_name', 'Flexible Route')->first();
            expect($route->stops)->toHaveCount(3);
            expect($route->boarding_points)->toHaveCount(2);
            expect($route->drop_off_points)->toHaveCount(2);
        });

        it('automatically generates route name from cities', function (): void {
            $this->actingAs($this->adminUser);

            // Test route name auto-generation
            livewire(CreateRoute::class)
                ->fillForm([
                    'origin_city' => 'Los Angeles',
                    'destination_city' => 'San Francisco',
                    'is_active' => true,
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('origin_city', 'Los Angeles')
                ->where('destination_city', 'San Francisco')
                ->first();
            expect($route->route_name)->toBe('Los Angeles to San Francisco');
        });

        it('can create inactive routes', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'route_name' => 'Seasonal Route',
                    'origin_city' => 'Miami',
                    'destination_city' => 'Orlando',
                    'is_active' => false,
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $route = Route::where('route_name', 'Seasonal Route')->first();
            expect($route->is_active)->toBeFalse();
        });

        it('validates required fields when creating route', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateRoute::class)
                ->fillForm([
                    'route_name' => '',
                    'origin_city' => '',
                    'destination_city' => '',
                ])
                ->call('create')
                ->assertHasFormErrors([
                    'route_name' => 'required',
                    'origin_city' => 'required',
                    'destination_city' => 'required',
                ]);
        });
    });

    describe('Route Listing', function (): void {
        it('can list routes through filament admin panel', function (): void {
            $this->actingAs($this->adminUser);

            Route::factory()->count(3)->create([
                'operator_id' => $this->operator->id,
            ]);

            livewire(ListRoutes::class)
                ->assertCanSeeTableRecords(Route::where('operator_id', $this->operator->id)->get());
        });

        it('cannot see other operators routes', function (): void {
            $this->actingAs($this->adminUser);

            // Create a route for our operator
            $myRoute = Route::factory()->create([
                'operator_id' => $this->operator->id,
                'route_name' => 'My Route',
            ]);

            $response = $this->get("/operator/{$this->operator->id}/routes");

            $response->assertStatus(200)
                ->assertSee($myRoute->route_name)
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
