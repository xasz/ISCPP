<?php

namespace Database\Factories;

use App\Models\ISCPPEventRule;
use App\Models\SCTenantGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ISCPPEventRule>
 */
class ISCPPEventRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'enabled' => true,
            'event_key' => 'sc.tenant.created',
            'tenantId' => null,
            'url' => 'https://hooks.example.com/'.fake()->uuid(),
            'basic_auth_username' => null,
            'basic_auth_password' => null,
            'consecutive_failures' => 0,
        ];
    }

    public function forEvent(string $key): static
    {
        return $this->state(fn () => ['event_key' => $key]);
    }

    public function forTenant(string $tenantId): static
    {
        return $this->state(fn () => ['tenantId' => $tenantId]);
    }

    public function forTenantGroup(SCTenantGroup $group): static
    {
        return $this->state(fn () => ['sc_tenant_group_id' => $group->id]);
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['enabled' => false, 'disabled_at' => now()]);
    }
}
