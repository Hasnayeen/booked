<?php

declare(strict_types=1);

use App\Filament\Operator\Resources\UserResource\Pages\EditUser;
use App\Filament\Operator\Resources\UserResource\Pages\ListUsers;
use App\Filament\Operator\Resources\UserResource\Pages\ViewUser;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

describe('Operator User Management Interface', function (): void {
    beforeEach(function (): void {
        Filament::setCurrentPanel(Filament::getPanel('operator'));

        $this->operator = Operator::factory()->create(['status' => 'approved']);

        // Use seeded roles instead of creating them
        $this->adminRole = Role::where('name', 'Operator Admin')->first();
        $this->staffRole = Role::where('name', 'Operator Staff')->first();

        $this->adminUser = User::factory()->create();
        $this->adminUser->operators()->attach($this->operator, ['role_id' => $this->adminRole->id]);

        $this->staffUser = User::factory()->create();
        $this->staffUser->operators()->attach($this->operator, ['role_id' => $this->staffRole->id]);

        $this->otherOperator = Operator::factory()->create(['status' => 'approved']);
        $this->otherUser = User::factory()->create();
        $this->otherUser->operators()->attach($this->otherOperator, ['role_id' => $this->staffRole->id]);

        // Authenticate user and set tenant context for Filament
        $this->actingAs($this->adminUser);
        Filament::setTenant($this->operator);
    });

    it('can list users belonging to current operator through filament admin panel', function (): void {
        $this->actingAs($this->adminUser);

        $component = livewire(ListUsers::class)
            ->assertSuccessful()
            ->assertSee($this->adminUser->name)
            ->assertSee($this->staffUser->name)
            ->assertDontSee($this->otherUser->name);
    });
    it('can view user details with role information through filament admin panel', function (): void {
        $this->actingAs($this->adminUser);

        livewire(ViewUser::class, [
            'record' => $this->staffUser->getRouteKey(),
            'tenant' => $this->operator,
        ])
            ->assertSee($this->staffUser->name)
            ->assertSee($this->staffUser->email)
            ->assertSee('Operator Staff');
    });

    it('can filter users by role through filament admin panel', function (): void {
        $this->actingAs($this->adminUser);

        livewire(ListUsers::class, ['tenant' => $this->operator])
            ->filterTable('role', $this->adminRole->id)
            ->assertCanSeeTableRecords([$this->adminUser])
            ->assertCannotSeeTableRecords([$this->staffUser]);
    });

    it('can search users by name and email through filament admin panel', function (): void {
        $this->actingAs($this->adminUser);

        livewire(ListUsers::class, ['tenant' => $this->operator])
            ->searchTable($this->staffUser->name)
            ->assertCanSeeTableRecords([$this->staffUser])
            ->assertCannotSeeTableRecords([$this->adminUser]);
    });

    it('can change user role within operator through filament admin panel', function (): void {
        $this->actingAs($this->adminUser);

        livewire(EditUser::class, [
            'record' => $this->staffUser->getRouteKey(),
            'tenant' => $this->operator,
        ])
            ->fillForm([
                'role_id' => $this->adminRole->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        expect($this->staffUser->fresh()->hasRoleInOperator($this->operator, 'Operator Admin'))->toBeTrue();
    });

    it('can remove user from operator through filament admin panel', function (): void {
        $this->actingAs($this->adminUser);

        livewire(ListUsers::class, ['tenant' => $this->operator])
            ->callTableAction('remove', $this->staffUser)
            ->assertHasNoTableActionErrors()
            ->assertNotified();

        assertDatabaseMissing('operator_user', [
            'operator_id' => $this->operator->id,
            'user_id' => $this->staffUser->id,
        ]);

        expect($this->staffUser->fresh()->belongsToOperator($this->operator))->toBeFalse();
    });

    it('prevents removing the last admin user through filament admin panel', function (): void {
        $this->actingAs($this->adminUser);

        livewire(ListUsers::class, ['tenant' => $this->operator])
            ->callTableAction('remove', $this->adminUser)
            ->assertHasTableActionErrors(['remove' => 'Cannot remove the last admin user']);

        assertDatabaseHas('operator_user', [
            'operator_id' => $this->operator->id,
            'user_id' => $this->adminUser->id,
        ]);
    });

    it('shows user statistics on operator user management page', function (): void {
        $this->actingAs($this->adminUser);

        livewire(ListUsers::class, ['tenant' => $this->operator])
            ->assertSee('Total Users: 2')
            ->assertSee('Admins: 1')
            ->assertSee('Staff: 1');
    });

    it('can bulk remove multiple users through filament admin panel', function (): void {
        $additionalUser = User::factory()->create();
        $additionalUser->operators()->attach($this->operator, ['role_id' => $this->staffRole->id]);

        $this->actingAs($this->adminUser);

        livewire(ListUsers::class, ['tenant' => $this->operator])
            ->callTableBulkAction('remove', [$this->staffUser, $additionalUser])
            ->assertHasNoTableActionErrors()
            ->assertNotified();

        assertDatabaseMissing('operator_user', [
            'operator_id' => $this->operator->id,
            'user_id' => $this->staffUser->id,
        ]);

        assertDatabaseMissing('operator_user', [
            'operator_id' => $this->operator->id,
            'user_id' => $additionalUser->id,
        ]);
    });

    it('only allows admin users to manage team members through filament admin panel', function (): void {
        $this->actingAs($this->staffUser);

        livewire(ListUsers::class, ['tenant' => $this->operator])
            ->assertForbidden();
    });
})->todo('Implement user role checks');
