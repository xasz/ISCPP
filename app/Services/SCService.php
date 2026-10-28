<?php

namespace App\Services;

use App\Models\Event;
use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Settings\SCServiceSettings;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

class SCService
{
    private $baseUrl = 'https://api.central.sophos.com';

    private $authUrl = 'https://id.sophos.com/api/v2/oauth2/token';

    private SCServiceSettings $settings;

    public function __construct(SCServiceSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Connect to the Sophos Partner API
     */
    private function authenticate()
    {
        if (! $this->settings->clientId || ! $this->settings->clientSecret) {
            Event::throwError('sctentant', 'Sophos Central Partner credentials not found');
        }

        $response = Http::asForm()->post($this->authUrl, [
            'client_id' => $this->settings->clientId,
            'client_secret' => decrypt($this->settings->clientSecret),
            'grant_type' => 'client_credentials',
            'scope' => 'token',
        ]);

        if (! $response->successful()) {
            Event::throwError('sc', 'Sophos Partner Authentification failed');
        }

        $data = $response->json();
        $this->settings->token = $data['access_token'];
        $this->settings->token_expires_at = time() + $data['expires_in'];
        $this->settings->refresh_token = $data['refresh_token'];

        Event::logInfo('sc', 'Sophos Central Connection successfull - established');

        $whoami = $this->whoami();
        if (! isset($whoami['idType'])) {
            $error = 'Unkown';
            if (isset($whoami['error'])) {
                $error = $whoami['error'];
            }
            Event::throwError('sctenants', 'Sophos Partner Authentification failed - Error: '.$error);
        }

        if ($whoami['idType'] != 'partner') {
            Event::throwError('sctenants', 'Sophos Partner Authentification successfull - But not no partner account - Account Type: '.$whoami['idType']);
        }

        $this->settings->partnerId = $whoami['id'];
        $this->settings->save();
    }

    /**
     * Get the current session ID and type
     */
    public function whoami()
    {
        $response = Http::withToken($this->bearer())->get($this->baseUrl.'/whoami/v1');

        return $response->json();
    }

    /**
     * Get the current bearer token
     * If the token is expired, it will be regenerated
     */
    private function bearer()
    {
        if ($this->settings->token_expires_at == null || time() >= $this->settings->token_expires_at) {
            Event::logInfo('sc', 'Sophos Partner Bearer Token expired, regenerating...');
            $this->authenticate();
        }

        return $this->settings->token;
    }

    /**
     * Get the result from the Sophos Partner API
     * Handles the http request and response with retries

     *
     * @param  $method  - the http method
     * @param  string  $url  - the url to call
     * @param  array  $options  - the query parameters
     * @param  bool  $raw  - return raw data
     */
    private function send($method, $url, $query, $options = ['skipStatusCodeCheck' => false])
    {
        $response = null;
        $attempt = 0;
        $maxRetries = 10;
        $base = 1000;
        $cap = 30000;

        while (true) {
            // The token is resolved per attempt, as it may expire while backing off.
            $http = Http::withToken($this->bearer());

            if (isset($options['partnerID'])) {
                $http = $http->withHeader('X-Partner-ID', $options['partnerID']);
            }

            if (isset($options['tenantID'])) {
                $http = $http->withHeader('X-Tenant-ID', $options['tenantID']);
            }

            Event::logDebug('sc', "Sophos Partner API Request: {$method} {$url} - Attempt: {$attempt} - Query: ".json_encode($query));
            switch ($method) {
                case 'GET':
                    $response = $http->get($url, $query);
                    break;
                case 'POST':
                    $response = $http->post($url, $query);
                    break;
                case 'DELETE':
                    $response = $http->delete($url, $query);
                    break;
                default:
                    throw new Exception("{$method} not supported");
                    break;
            }

            $isRetryable = $response->tooManyRequests() || $response->serverError();
            if (! $isRetryable || $attempt >= $maxRetries) {
                break;
            }

            // Exponential backoff with full jitter, as recommended by Sophos.
            $backoff = random_int(0, min($cap, $base * (2 ** $attempt)));
            Event::logWarning('sc', "Sophos Partner API returned {$response->status()}, backoff for {$backoff} ms");
            Sleep::for($backoff)->milliseconds();
            $attempt++;
        }

        Event::logDebug('sc', "Sophos Partner API Request completed - Status: {$response->status()}");

        if (! $response->successful() && empty($options['skipStatusCodeCheck'])) {
            throw new Exception("Sophos Partner API Error: {$response->status()} - {$method} {$url} - ".$response->body());
        }

        Event::logDebug('sc', 'Return response: '.json_encode($response->json()));

        return $response->json();
    }
    /*
    private function get($url, $query, $options){
        return $this->send('GET', $url, $query, $options);
    }

    private function post($url, $query, $options){
        return $this->send('POST', $url, $query, $options);
    }
    */

    /**
     * Possible filter options:
     * - url (string) - the url to call
     * - pageing (bool) - do autopaging items
     * - raw (bool) - return raw json data
     * - tenantID (string) - the tenant ID
     *
     * @param  array  $options
     * @return result
     */
    private function tenantSend($method, $tenant, $url, $options = [])
    {
        $url = $tenant['apiHost'].$url;

        $query = [];
        if (isset($options['pageing'])) {
            $query['pageTotal'] = $options['pageing'];
        }

        if (isset($options['pageByKey'])) {
            $query['pageTotal'] = $options['pageByKey'];
        }

        if (isset($options['query'])) {
            $query = array_merge($query, $options['query']);
        }

        $preparedOptions = [
            'tenantID' => $tenant->id,
            'skipStatusCodeCheck' => $options['skipStatusCodeCheck'] ?? false,
        ];

        if (isset($options['raw']) && $options['raw'] == true) {
            return $this->send($method, $url, $query, $preparedOptions);
        }

        $result = $this->send($method, $url, $query, $preparedOptions);

        $items = collect($result['items']);

        if (isset($options['pageing']) && $options['pageing'] == true) {
            $totalPages = $result['pages']['total'];
            for ($p = 2; $p <= $totalPages; $p++) {
                $query['page'] = $p;
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }

        if (isset($options['pageByKey']) && $options['pageByKey'] == true) {
            $totalPages = $result['pages']['total'];
            for ($p = 2; $p <= $totalPages; $p++) {
                $query['pageFromKey'] = $result['pages']['nextKey'];
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }

        return $items;
    }

    public function partnerSend($method, $url, $options = [])
    {

        $url = $this->baseUrl.$url;

        $query = [];
        if (isset($options['pageing'])) {
            $query['pageTotal'] = $options['pageing'];
        }

        if (isset($options['pageByKey'])) {
            $query['pageTotal'] = $options['pageByKey'];
        }

        $preparedOptions = [
            'partnerID' => $this->settings->partnerId,
            'skipStatusCodeCheck' => $options['skipStatusCodeCheck'] ?? false,
        ];

        if (isset($options['raw']) && $options['raw'] == true) {
            return $this->send($method, $url, $query, $preparedOptions);
        }

        $result = $this->send($method, $url, $query, $preparedOptions);

        $items = collect($result['items']);

        if (isset($options['pageing']) && $options['pageing'] == true) {
            $totalPages = $result['pages']['total'];
            for ($p = 2; $p <= $totalPages; $p++) {
                $query['page'] = $p;
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }

        if (isset($options['pageByKey']) && $options['pageByKey'] == true) {
            $totalPages = $result['pages']['total'];
            for ($p = 2; $p <= $totalPages; $p++) {
                $query['pageFromKey'] = $result['pages']['nextKey'];
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }

        return $items;
    }

    public function partnerGet($url, $options = [])
    {
        return $this->partnerSend('GET', $url, $options);
    }

    public function partnerPost($url, $options = [])
    {
        return $this->partnerSend('POST', $url, $options);
    }

    public function tenantGet(SCTenant $tenant, $url, $options = [])
    {
        return $this->tenantSend('GET', $tenant, $url, $options);
    }

    public function tenantPost(SCTenant $tenant, $url, $options = [])
    {
        return $this->tenantSend('POST', $tenant, $url, $options);
    }

    public function tenantDelete(SCTenant $tenant, $url, $options = [])
    {
        return $this->tenantSend('DELETE', $tenant, $url, $options);
    }

    public function tenants($filter = ['IncludeTrail' => false, 'IncludeInactive' => false])
    {
        $items = $this->partnerGet('/partner/v1/tenants', [
            'pageing' => true,
        ]);

        return $items;
    }

    public function tenant(string $uuid)
    {
        return $this->partnerGet('/partner/v1/tenants/{$uuid}');
    }

    public function accountHealthCheck(SCTenant $tenant)
    {
        return $this->tenantGet($tenant, '/account-health-check/v1/health-check', ['raw' => true]);
    }

    public function alerts(SCTenant $tenant, $from)
    {

        return $this->tenantGet($tenant, '/common/v1/alerts', [
            'pageByKey' => true,
            'query' => [
                'from' => $from->isoFormat('YYYY-MM-DDTHH:mm:ss.000[Z]'),
                'to' => now()->isoFormat('YYYY-MM-DDTHH:mm:ss.000[Z]'),
            ],
        ]);
    }

    public function billingUsage(int $month, int $year)
    {
        $urlYear = urlencode($year);
        $urlMonth = urlencode($month);

        return $this->partnerGet("/partner/v1/billing/usage/{$urlYear}/{$urlMonth}",
            ['pageByKey' => true]
        );
    }

    public function endpoints(SCTenant $tenant)
    {
        return $this->tenantGet($tenant, '/endpoint/v1/endpoints', [
            'pageByKey' => true,
        ]);
    }

    /**
     * A single endpoint. "full" includes everything the list view leaves out.
     *
     * @return array<string, mixed>
     */
    public function endpoint(SCTenant $tenant, string $endpointId, string $view = 'full'): array
    {
        return $this->tenantGet($tenant, '/endpoint/v1/endpoints/'.urlencode($endpointId), [
            'raw' => true,
            'query' => ['view' => $view],
        ]);
    }

    /**
     * Tamper protection of an endpoint. The response contains the current password in
     * clear text, so callers must not persist it or put it into client side state.
     *
     * @return array{enabled?: bool, password?: string, previousPasswords?: array<int, array{password: string, invalidatedAt: string}>}
     */
    public function endpointTamperProtection(SCTenant $tenant, string $endpointId): array
    {
        return $this->tenantGet($tenant, '/endpoint/v1/endpoints/'.urlencode($endpointId).'/tamper-protection', ['raw' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    public function endpointIsolation(SCTenant $tenant, string $endpointId): array
    {
        return $this->tenantGet($tenant, '/endpoint/v1/endpoints/'.urlencode($endpointId).'/isolation', ['raw' => true]);
    }

    public function endpointPolicies(SCTenant $tenant): Collection
    {
        return $this->tenantGet($tenant, '/endpoint/v1/policies', ['pageing' => true]);
    }

    public function blockedItems(SCTenant $tenant): Collection
    {
        return $this->tenantGet($tenant, '/endpoint/v1/settings/blocked-items', ['pageing' => true]);
    }

    public function blockedAddresses(SCTenant $tenant): Collection
    {
        return $this->tenantGet($tenant, '/endpoint/v1/settings/blocked-addresses', ['pageing' => true]);
    }

    public function firewalls(SCTenant $tenant)
    {
        return $this->tenantGet($tenant, '/firewall/v1/firewalls', [
            'pageing' => true,
        ]);
    }

    /**
     * Start a full configuration export. Sophos answers with a transaction id; the
     * download link appears on that transaction once the firewall has finished.
     */
    public function firewallConfigExport(SCTenant $tenant, string $firewallId): string
    {
        $response = $this->tenantPost($tenant, '/firewall/v1/firewall-config/firewalls/'.urlencode($firewallId).'/export', [
            'raw' => true,
            'query' => ['fullExport' => true],
        ]);

        $transactionId = $response['transactionId'] ?? null;

        if (! is_string($transactionId) || $transactionId === '') {
            throw new Exception('Sophos Partner API Error: firewall export returned no transaction id');
        }

        return $transactionId;
    }

    /**
     * @return array<string, mixed>
     */
    public function firewallConfigTransaction(SCTenant $tenant, string $firewallId, string $transactionId): array
    {
        return $this->tenantGet($tenant, '/firewall/v1/firewall-config/firewalls/'.urlencode($firewallId).'/transactions/'.urlencode($transactionId), [
            'raw' => true,
            'query' => ['fields' => 'request,response,expiryAt,finishedAt'],
        ]);
    }

    /**
     * Download an exported configuration from the pre-signed URL Sophos hands out.
     *
     * The URL is signed on its own, so no bearer token is sent with it.
     */
    public function downloadFirewallConfigExport(string $url): string
    {
        $response = Http::timeout(120)->get($url);

        if (! $response->successful()) {
            throw new Exception("Firewall config download failed: {$response->status()}");
        }

        return $response->body();
    }

    public function firewallFirmwareUpgradeCheck(SCTenant $tenant, array $firewallIds): array
    {
        $response = $this->tenantPost($tenant, '/firewall/v1/firewalls/actions/firmware-upgrade-check', [
            'raw' => true,
            'skipStatusCodeCheck' => true,
            'query' => [
                'firewalls' => array_values(array_unique($firewallIds)),
            ],
        ]);

        return is_array($response) ? $response : [];
    }

    public function firewallFirmwareUpgradePlan(
        SCTenant $tenant,
        array $firewallIds,
        ?string $upgradeToVersion,
        CarbonInterface $upgradeAt,
    ): array {
        $upgradeAtZulu = $upgradeAt
            ->copy()
            ->utc()
            ->format('Y-m-d\TH:i:s.000\Z');

        $firewalls = collect(array_values(array_unique($firewallIds)))
            ->map(function (string $firewallId) use ($upgradeAtZulu, $upgradeToVersion): array {
                $payload = [
                    'id' => $firewallId,
                    'upgradeAt' => $upgradeAtZulu,
                ];

                if ($upgradeToVersion !== null && $upgradeToVersion !== '') {
                    $payload['upgradeToVersion'] = $upgradeToVersion;
                }

                return $payload;
            })
            ->all();

        $response = $this->tenantPost($tenant, '/firewall/v1/firewalls/actions/firmware-upgrade', [
            'raw' => true,
            'skipStatusCodeCheck' => true,
            'query' => [
                'firewalls' => $firewalls,
            ],
        ]);

        return is_array($response) ? $response : [];
    }

    public function firewallFirmwareUpgradeCancel(SCTenant $tenant, array $firewallIds): array
    {
        $ids = implode(',', $firewallIds);
        $response = $this->tenantDelete($tenant, '/firewall/v1/firewalls/actions/firmware-upgrade?ids='.$ids, ['raw' => true, 'skipStatusCodeCheck' => true]);

        return is_array($response) ? $response : [];
    }

    public function tenantDownloads(SCTenant $tenant)
    {
        return collect($this->tenantGet($tenant, '/endpoint/v1/downloads', ['raw' => true]));
    }

    public function tenantHealthscore(SCTenant $tenant)
    {
        return collect($this->tenantGet($tenant, '/account-health-check/v1/health-check', ['raw' => true]));
    }

    public function alertsAction(SCTenant $tenant, SCAlert $alert, $action, $message = '')
    {
        $alertId = urlencode($alert->id);

        $response = $this->tenantPost($tenant, "/common/v1/alerts/{$alertId}/actions", [
            'raw' => true,
            'skipStatusCodeCheck' => true,
            'query' => [
                'action' => $action,
                'message' => $message,
            ],
        ]);

        $response = collect($response);

        if ($response->get('status', '') == 'completed') {
            return;
        }

        if ($response->get('error', '') == 'resourceNotFound') {
            return;
        }

        Event::throwError('scalerts', 'Sophos Partner API Alert Action Error: '.$response->toJson());
    }

    public function fakebillingUsage(int $month, int $year)
    {

        $date = now()->setYear($year)->setMonth($month);

        $sctenants = SCTenant::notIgnored()->get();

        $data = collect();
        foreach ($sctenants as $sct) {
            for ($i = 0; $i < rand(1, 15); $i++) {
                $data->push([
                    'orderLineItemNumber' => fake()->uuid(),
                    'productGroup' => fake()->word(),
                    'billableQuantity' => fake()->randomNumber(2),
                    'productCode' => fake()->isbn13(),
                    'sku' => fake()->word(),
                    'productDescription' => fake()->word(),
                    'accountId' => $sct->id,
                    'partnerAccountId' => fake()->uuid(),
                    'partnerEdiAccountNumber' => fake()->uuid(),
                    'partnerAccountName' => 'Partner Account Name',
                    'billingStartDate' => $date->startOfMonth(),
                    'billingEndDate' => $date->endOfMonth(),
                    'orderNumber' => fake()->numerify(),
                    'accountNumberOrSerialNumber' => fake()->word(),
                    'assignedAccountIdentifier' => $sct->name,
                    'contactStreet' => fake()->streetAddress(),
                    'contactCity' => fake()->city(),
                    'contactCountry' => fake()->country(),
                    'contactZip' => fake()->postcode(),
                    'destributorAccountName' => fake()->word(),
                    'externalId' => $sct->id,
                    'orderedQuantity' => 0,
                    'actualQuantity' => 0,

                ]);
            }
        }

        return $data->toArray();
    }
}
