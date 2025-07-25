<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Operator Admin', 'Operator Staff', 'Manager', 'Support']),
            'is_default' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Operator Admin',
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Operator Staff',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }
}
