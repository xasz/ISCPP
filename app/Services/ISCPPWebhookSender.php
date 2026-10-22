<?php

namespace App\Services;

use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Builds and sends the ISCPP webhook request.
 *
 * Kept separate from the job so payload encoding lives in exactly one place. The delivery
 * row deliberately does not store the request body, so reproducing a request for
 * debugging means re-encoding the stored envelope, which only matches if both sides use
 * identical flags.
 */
class ISCPPWebhookSender
{
    private const ENCODE_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function encode(array $envelope): string
    {
        return json_encode($envelope, self::ENCODE_FLAGS);
    }

    public function send(ISCPPEventRule $rule, ISCPPEventLog $event): Response
    {
        $this->assertUrlIsAllowed($rule->url);

        $body = $this->encode($event->payload ?? []);

        return Http::withBody($body, 'application/json')
            ->timeout((int) config('iscpp.webhook.timeout', 10))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Idempotency-Key' => $event->uuid,
                'X-ISCPP-Event' => $event->key,
            ])
            ->when(filled($rule->basic_auth_username), fn ($request) => $request->withBasicAuth(
                (string) $rule->basic_auth_username,
                (string) $rule->basic_auth_password,
            ))
            ->post($rule->url);
    }

    /**
     * Reject URLs that would let a webhook reach the host it runs on.
     *
     * The legacy webhook had a single admin set URL; this is an unbounded list, so the
     * check runs both on save and here at send time.
     *
     * @throws \RuntimeException
     */
    public function assertUrlIsAllowed(?string $url): void
    {
        if (($error = $this->urlRejectionReason($url)) !== null) {
            throw new \RuntimeException($error);
        }
    }

    public function urlRejectionReason(?string $url): ?string
    {
        if (blank($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return __('The webhook URL is not a valid URL.');
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return __('The webhook URL must use http or https.');
        }

        if (config('iscpp.webhook.allow_private_hosts', false)) {
            return null;
        }

        $host = (string) parse_url($url, PHP_URL_HOST);

        if ($host === '') {
            return __('The webhook URL has no host.');
        }

        if (in_array(strtolower($host), ['localhost', 'localhost.localdomain'], true)) {
            return __('The webhook URL may not point at a private or loopback address.');
        }

        foreach ($this->resolveAddresses($host) as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return __('The webhook URL may not point at a private or loopback address.');
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function resolveAddresses(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $records = @dns_get_record($host, DNS_A + DNS_AAAA) ?: [];

        return collect($records)
            ->map(fn (array $record) => $record['ip'] ?? $record['ipv6'] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
