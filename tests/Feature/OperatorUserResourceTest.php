<?php

use Filament\Schemas\Schema;
use App\Filament\Operator\Resources\UserResource;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;

describe('Operator User Management Resource', function (): void {
    beforeEach(function (): void {
        $this->operator = Operator::factory()->create(['status' => 'approved']);
        $this->adminRole = Role::firstOrCreate(['name' => 'Operator Admin'], ['is_default' => false]);
        $this->staffRole = Role::firstOrCreate(['name' => 'Operator Staff'], ['is_default' => false]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->operators()->attach($this->operator, ['role_id' => $this->adminRole->id]);

        $this->staffUser = User::factory()->create();
        $this->staffUser->operators()->attach($this->operator, ['role_id' => $this->staffRole->id]);

        $this->otherOperator = Operator::factory()->create(['status' => 'approved']);
        $this->otherUser = User::factory()->create();
        $this->otherUser->operators()->attach($this->otherOperator, ['role_id' => $this->staffRole->id]);
    });

    it('can get the correct model class', function (): void {
        expect(UserResource::getModel())->toBe(User::class);
    });

    it('has correct navigation settings', function (): void {
        expect(UserResource::getNavigationGroup())->toBe('Team Management');
        expect(UserResource::getNavigationLabel())->toBe('Team Members');
    });

    it('defines correct form schema', function (): void {
        $schema = UserResource::form(new Schema);
        expect($schema)->toBeInstanceOf(Schema::class);
    });

    it('can create pages', function (): void {
        $pages = UserResource::getPages();

        expect($pages)->toHaveKey('index');
        expect($pages)->toHaveKey('view');
        expect($pages)->toHaveKey('edit');
    });

    it('can get table configuration', function (): void {
        // Just check that the method exists and returns a table
        $tableMethod = new ReflectionMethod(UserResource::class, 'table');
        expect($tableMethod->isStatic())->toBeTrue();
        expect($tableMethod->isPublic())->toBeTrue();
    });
})->todo();
