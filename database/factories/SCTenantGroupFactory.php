<?php

namespace Database\Factories;

use App\Models\SCTenant;
use App\Models\SCTenantGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SCTenantGroup>
 */
class SCTenantGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => null,
        ];
    }

    /**
     * @param  iterable<int, SCTenant>  $tenants
     */
    public function withTenants(iterable $tenants): static
    {
        return $this->afterCreating(function (SCTenantGroup $group) use ($tenants) {
            $group->tenants()->attach(collect($tenants)->pluck('id')->all());
        });
    }
}
