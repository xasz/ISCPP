<?php

namespace Database\Factories;

use App\Models\SCFirewall;
use App\Models\SCFirewallBackup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SCFirewallBackup>
 */
class SCFirewallBackupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firewallId' => (string) Str::uuid(),
            'tenantId' => (string) Str::uuid(),
            'status' => SCFirewallBackup::STATUS_PENDING,
            'transactionId' => (string) Str::uuid(),
            'requested_by' => 'test',
        ];
    }

    public function forFirewall(SCFirewall $firewall): static
    {
        return $this->state(fn () => [
            'firewallId' => $firewall->id,
            'tenantId' => $firewall->tenantId,
        ]);
    }

    public function finished(string $path = 'firewall-backups/test.tar.gz'): static
    {
        return $this->state(fn () => [
            'status' => SCFirewallBackup::STATUS_FINISHED,
            'file_path' => $path,
            'file_size' => 4,
            'finished_at' => now(),
        ]);
    }

    public function failed(string $error = 'Export failed'): static
    {
        return $this->state(fn () => [
            'status' => SCFirewallBackup::STATUS_FAILED,
            'error' => $error,
            'finished_at' => now(),
        ]);
    }
}
