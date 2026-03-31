<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnonymizeExistingScDataSeeder extends Seeder
{
    /**
     * Anonymize existing SC data in-place while preserving classifications and counts.
     */
    public function run(): void
    {
        if (! $this->hasAnyScData()) {
            return;
        }

        $this->disableForeignKeys();

        try {

            foreach (DB::table('sc_tenants')->get() as $tenant) {
                $payload = $this->decodeJson($tenant->rawData ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('sc_tenants')
                    ->where('id', $tenant->id)
                    ->update([
                        'showAs' => $this->anonName((string) $tenant->showAs, 'Tenant'),
                        'name' => $this->anonName((string) $tenant->name, 'Tenant'),
                        'partnerId' => $this->mapUuidValue($tenant->partnerId),
                        'organizationId' => $this->mapUuidValue($tenant->organizationId),
                        'rawData' => $this->encodeJson($payload),
                    ]);
            }

            foreach (DB::table('sc_alerts')->get() as $alert) {
                $payload = $this->decodeJson($alert->rawData ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('sc_alerts')
                    ->where('id', $alert->id)
                    ->update([
                        'groupKey' => $this->mapUuidValue($alert->groupKey),
                        'managedAgentID' => $this->mapUuidValue($alert->managedAgentID),
                        'managedAgentName' => $this->anonName((string) ($alert->managedAgentName ?? ''), 'Agent'),
                        'personID' => $this->mapUuidValue($alert->personID),
                        'personName' => $this->anonName((string) ($alert->personName ?? ''), 'Person'),
                        'rawData' => $this->encodeJson($payload),
                    ]);
            }

            foreach (DB::table('sc_endpoints')->get() as $endpoint) {
                $payload = $this->decodeJson($endpoint->rawData ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('sc_endpoints')
                    ->where('id', $endpoint->id)
                    ->update([
                        'hostname' => $this->anonHost((string) ($endpoint->hostname ?? 'endpoint.local'), 'endpoint'),
                        'rawData' => $this->encodeJson($payload),
                    ]);
            }

            foreach (DB::table('sc_firewalls')->get() as $firewall) {
                $payload = $this->decodeJson($firewall->rawData ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('sc_firewalls')
                    ->where('id', $firewall->id)
                    ->update([
                        'hostname' => $this->anonHost((string) ($firewall->hostname ?? 'firewall.local'), 'firewall'),
                        'rawData' => $this->encodeJson($payload),
                    ]);
            }

            foreach (DB::table('sc_billable')->get() as $billable) {
                $payload = $this->decodeJson($billable->rawData ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('sc_billable')
                    ->where('id', $billable->id)
                    ->update([
                        'rawData' => $this->encodeJson($payload),
                    ]);
            }

            foreach (DB::table('sc_tenant_downloads')->get() as $download) {
                $payload = $this->decodeJson($download->rawData ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('sc_tenant_downloads')
                    ->where('id', $download->id)
                    ->update([
                        'rawData' => $this->encodeJson($payload),
                    ]);
            }

            foreach (DB::table('sc_tenant_healthscores')->get() as $healthscore) {
                $payload = $this->decodeJson($healthscore->rawData ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('sc_tenant_healthscores')
                    ->where('id', $healthscore->id)
                    ->update([
                        'rawData' => $this->encodeJson($payload),
                    ]);
            }

            foreach (DB::table('webhook_log')->get() as $log) {
                $payload = $this->decodeJson($log->payload ?? null);
                $payload = $this->sanitizePayload($payload);

                DB::table('webhook_log')
                    ->where('id', $log->id)
                    ->update([
                        'payload' => $this->encodeJson($payload),
                    ]);
            }
        } finally {
            $this->enableForeignKeys();
        }
    }

    private function hasAnyScData(): bool
    {
        return DB::table('sc_tenants')->exists()
            || DB::table('sc_alerts')->exists()
            || DB::table('sc_endpoints')->exists()
            || DB::table('sc_firewalls')->exists();
    }

    private function disableForeignKeys(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
    }

    private function enableForeignKeys(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function mapUuidValue(?string $value): ?string
    {
        if (! $value || ! $this->looksLikeUuid($value)) {
            return $value;
        }

        return (string) Str::uuid();
    }

    private function looksLikeUuid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^[0-9a-fA-F-]{36}$/', $value);
    }

    private function anonName(string $value, string $prefix): string
    {
        if (trim($value) === '') {
            return $value;
        }

        return sprintf('%s %s', $prefix, strtoupper(substr(sha1($value), 0, 8)));
    }

    private function anonHost(string $value, string $prefix): string
    {
        if (trim($value) === '') {
            return $value;
        }

        return sprintf('%s-%s.local', $prefix, strtolower(substr(sha1($value), 0, 10)));
    }

    /**
     * @return array<mixed>|null
     */
    private function decodeJson(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function encodeJson(?array $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    private function sanitizePayload(mixed $value, ?string $key = null): mixed
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $childKey => $childValue) {
                $sanitized[$childKey] = $this->sanitizePayload($childValue, is_string($childKey) ? $childKey : null);
            }

            return $sanitized;
        }

        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $normalized = strtolower((string) $key);

        if (in_array($normalized, ['name', 'showas', 'personname', 'managedagentname', 'hostname'], true)) {
            return $this->anonName($value, 'Anon');
        }

        if (str_ends_with($normalized, 'id') || in_array($normalized, ['id', 'personid', 'tenantid'], true)) {
            return $this->looksLikeUuid($value) ? (string) Str::uuid() : strtoupper(substr(sha1($value), 0, 12));
        }

        return $value;
    }
}
