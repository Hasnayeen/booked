<?php

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Filament\Operator\Resources\BusResource;
use App\Filament\Operator\Resources\BusResource\Pages\CreateBus;
use App\Filament\Operator\Resources\BusResource\Pages\EditBus;
use App\Filament\Operator\Resources\BusResource\Pages\ListBuses;
use App\Filament\Operator\Resources\BusResource\Pages\ViewBus;
use App\Models\Bus;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

describe('Operator Bus Management', function (): void {
    beforeEach(function (): void {
        $this->operator = Operator::factory()->create(['status' => 'approved']);
        $this->otherOperator = Operator::factory()->create(['status' => 'approved']);

        $this->adminRole = Role::firstOrCreate(['name' => 'Operator Admin']);
        $this->staffRole = Role::firstOrCreate(['name' => 'Operator Staff']);
        $this->roleWithoutPermission = Role::firstOrCreate(['name' => 'Operator Without Permission']);

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->operators()->attach($this->operator, ['role_id' => $this->adminRole->id]);

        $this->staffUser = User::factory()->create();
        $this->staffUser->operators()->attach($this->operator, ['role_id' => $this->staffRole->id]);

        $this->otherOperatorUser = User::factory()->create();
        $this->otherOperatorUser->operators()->attach($this->otherOperator, ['role_id' => $this->adminRole->id]);

        // Create sample buses
        $this->bus = Bus::factory()->create(['operator_id' => $this->operator->id]);
        $this->otherBus = Bus::factory()->create(['operator_id' => $this->otherOperator->id]);

        filament()->setCurrentPanel('operator');
        $this->actingAs($this->adminUser);
        filament()->setTenant($this->operator);
    });

    describe('Bus Resource Configuration', function (): void {
        it('has correct model class', function (): void {
            expect(BusResource::getModel())->toBe(Bus::class);
        });

        it('has correct navigation settings', function (): void {
            expect(BusResource::getNavigationGroup())->toBe('Fleet Management');
            expect(BusResource::getNavigationLabel())->toBe('Buses');
            expect(BusResource::getModelLabel())->toBe('Bus');
            expect(BusResource::getPluralModelLabel())->toBe('Buses');
        });

        it('can create resource pages', function (): void {
            $pages = BusResource::getPages();

            expect($pages)->toHaveKey('index');
            expect($pages)->toHaveKey('create');
            expect($pages)->toHaveKey('view');
            expect($pages)->toHaveKey('edit');

            expect($pages['index']->getPage())->toBe(ListBuses::class);
            expect($pages['create']->getPage())->toBe(CreateBus::class);
            expect($pages['view']->getPage())->toBe(ViewBus::class);
            expect($pages['edit']->getPage())->toBe(EditBus::class);
        });
    });

    describe('Bus Listing and Filtering', function (): void {
        it('can list buses through filament admin panel', function (): void {
            $this->actingAs($this->staffUser);

            $this->get(route('filament.operator.resources.buses.index', ['tenant' => $this->operator]))
                ->assertSuccessful()
                ->assertSee($this->bus->bus_number)
                ->assertSee($this->bus->category->value)
                ->assertSee($this->bus->type->value)
                ->assertSee($this->bus->total_seats)
                ->assertDontSee($this->otherBus->bus_number);
        });

        it('can filter buses by category through filament admin panel', function (): void {
            $luxuryBus = Bus::factory()->create([
                'operator_id' => $this->operator->id,
                'category' => BusCategory::LUXURY,
            ]);

            $standardBus = Bus::factory()->create([
                'operator_id' => $this->operator->id,
                'category' => BusCategory::STANDARD,
            ]);

            livewire(ListBuses::class)
                ->filterTable('category', 'luxury')
                ->assertCanSeeTableRecords([$luxuryBus])
                ->assertCanNotSeeTableRecords([$standardBus]);
        });

        it('can filter buses by type through filament admin panel', function (): void {
            $acBus = Bus::factory()->create([
                'operator_id' => $this->operator->id,
                'type' => BusType::AC,
            ]);

            $nonAcBus = Bus::factory()->create([
                'operator_id' => $this->operator->id,
                'type' => BusType::NON_AC,
            ]);

            livewire(ListBuses::class)
                ->filterTable('type', 'ac')
                ->assertCanSeeTableRecords([$acBus])
                ->assertCanNotSeeTableRecords([$nonAcBus]);
        });

        it('can filter buses by status through filament admin panel', function (): void {
            $activeBus = Bus::factory()->create([
                'operator_id' => $this->operator->id,
                'is_active' => true,
            ]);

            $inactiveBus = Bus::factory()->create([
                'operator_id' => $this->operator->id,
                'is_active' => false,
            ]);

            livewire(ListBuses::class)
                ->filterTable('is_active', '1')
                ->assertCanSeeTableRecords([$activeBus])
                ->assertCanNotSeeTableRecords([$inactiveBus]);
        });
    });

    describe('Bus Creation', function (): void {
        it('can create bus through filament admin panel', function (): void {
            Notification::fake();
            Repeater::fake();

            $busData = [
                'bus_number' => 'ABC-123',
                'category' => BusCategory::LUXURY->value,
                'type' => BusType::AC->value,
                'total_seats' => 45,
                'license_plate' => 'LIC-123',
                'is_active' => true,
                'amenities' => ['WiFi', 'AC', 'Reclining Seats'],
            ];

            livewire(CreateBus::class)
                ->fillForm($busData)
                ->call('create')
                ->assertHasNoFormErrors()
                ->assertNotified()
                ->assertRedirect();

            $createdBus = Bus::where('bus_number', 'ABC-123')->first();
            expect($createdBus)->not->toBeNull();
            expect($createdBus->operator_id)->toBe($this->operator->id);
            expect($createdBus->category)->toBe(BusCategory::LUXURY);
            expect($createdBus->type)->toBe(BusType::AC);
            expect($createdBus->total_seats)->toBe(45);
            expect($createdBus->license_plate)->toBe('LIC-123');
            expect($createdBus->is_active)->toBeTrue();
        });

        it('validates required fields when creating bus through filament admin panel', function (): void {
            $this->actingAs($this->adminUser);

            livewire(CreateBus::class)
                ->fillForm([
                    'bus_number' => '',
                    'total_seats' => null,
                ])
                ->call('create')
                ->assertHasFormErrors([
                    'bus_number' => 'required',
                    'total_seats' => 'required',
                ]);
        });

        it('validates unique bus number when creating bus through filament admin panel', function (): void {
            $existingBus = Bus::factory()->create([
                'operator_id' => $this->operator->id,
                'bus_number' => 'UNIQUE-123',
            ]);

            $this->actingAs($this->adminUser);

            livewire(CreateBus::class)
                ->fillForm([
                    'bus_number' => 'UNIQUE-123',
                    'category' => BusCategory::STANDARD->value,
                    'type' => BusType::AC->value,
                    'total_seats' => 40,
                ])
                ->call('create')
                ->assertHasFormErrors(['bus_number']);
        });

        it('restricts access for users without bus_manage permission', function (): void {
            $this->actingAs($this->staffUser);
            $this->staffUser->operators()->syncWithPivotValues($this->operator->id, ['role_id' => $this->roleWithoutPermission->id]);

            livewire(CreateBus::class)
                ->assertForbidden();
        });
    });

    describe('Bus Viewing', function (): void {
        it('can view bus details through filament admin panel', function (): void {
            livewire(ViewBus::class, ['record' => $this->bus->getRouteKey()])
                ->assertSuccessful()
                ->assertFormSet([
                    'bus_number' => $this->bus->bus_number,
                    'category' => $this->bus->category->value,
                    'type' => $this->bus->type->value,
                    'total_seats' => $this->bus->total_seats,
                ]);
        });

        it('cannot view other operators buses through filament admin panel', function (): void {
            filament()->setTenant($this->otherOperator);

            livewire(ViewBus::class, ['record' => $this->otherBus->getRouteKey()])
                ->assertForbidden();
        });
    });

    describe('Bus Editing', function (): void {
        it('can edit bus through filament admin panel', function (): void {
            $updatedData = [
                'total_seats' => 50,
                'is_active' => false,
            ];

            livewire(EditBus::class, ['record' => $this->bus->getRouteKey()])
                ->fillForm($updatedData)
                ->call('save')
                ->assertHasNoFormErrors()
                ->assertNotified();

            $this->bus->refresh();
            expect($this->bus->total_seats)->toBe(50);
            expect($this->bus->is_active)->toBeFalse();
        });

        it('cannot edit other operators buses through filament admin panel', function (): void {
            filament()->setTenant($this->otherOperator);

            livewire(EditBus::class, ['record' => $this->otherBus->getRouteKey()])
                ->assertForbidden();
        });

        it('restricts editing for users without bus_manage permission', function (): void {
            $this->actingAs($this->staffUser);
            $this->staffUser->operators()->syncWithPivotValues($this->operator->id, ['role_id' => $this->roleWithoutPermission->id]);

            livewire(EditBus::class, ['record' => $this->bus->getRouteKey()])
                ->assertForbidden();
        });
    });

    describe('Bus Authorization', function (): void {
        it('allows users with bus_manage permission to manage buses', function (): void {
            expect($this->adminUser->can('viewAny', Bus::class))->toBeTrue();
            expect($this->adminUser->can('create', Bus::class))->toBeTrue();
            expect($this->adminUser->can('view', $this->bus))->toBeTrue();
            expect($this->adminUser->can('update', $this->bus))->toBeTrue();
            expect($this->adminUser->can('delete', $this->bus))->toBeTrue();
        });

        it('restricts users without bus_manage permission from managing buses', function (): void {
            $this->actingAs($this->staffUser);
            $this->staffUser->operators()->syncWithPivotValues($this->operator->id, ['role_id' => $this->roleWithoutPermission->id]);

            expect($this->staffUser->can('create', Bus::class))->toBeFalse();
            expect($this->staffUser->can('update', $this->bus))->toBeFalse();
            expect($this->staffUser->can('delete', $this->bus))->toBeFalse();
        });

        it('prevents access to other operators buses', function (): void {
            filament()->setTenant($this->otherOperator);

            expect($this->adminUser->can('view', $this->otherBus))->toBeFalse();
            expect($this->adminUser->can('update', $this->otherBus))->toBeFalse();
            expect($this->adminUser->can('delete', $this->otherBus))->toBeFalse();
        });
    });
});
