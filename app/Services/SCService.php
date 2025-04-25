<?php

namespace App\Services;

use App\Models\Event;
use App\Models\SCTenant;
use App\Settings\SCServiceSettings;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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
        if(!$this->settings->clientId || !$this->settings->clientSecret){
            Event::throwError('sctentant', 'Sophos Central Partner credentials not found');
        }

        $response = Http::asForm()->post($this->authUrl, [
            'client_id' => $this->settings->clientId,
            'client_secret' => decrypt($this->settings->clientSecret),
            'grant_type' => 'client_credentials',
            'scope' => 'token'
        ]);
            
        if(!$response->successful()){
            Event::throwError('sc', 'Sophos Partner Authentification failed');
        }
        
        $data = $response->json();
        $this->settings->token = $data['access_token'];
        $this->settings->token_expires_at = time() + $data['expires_in'];
        $this->settings->refresh_token = $data['refresh_token'];

        Log::info("Sophos Central Connection successfull - established");

        $whoami = $this->whoami();
        if(!isset($whoami['idType'])){
            $error = "Unkown";
            if(isset($whoami['error'])){
                $error = $whoami['error'];
            }
            Event::throwError('sctenants', "Sophos Partner Authentification failed - Error: " . $error);
        }

        if($whoami['idType'] != 'partner'){
            Event::throwError('sctenants', "Sophos Partner Authentification successfull - But not no partner account - Account Type: " . $whoami['idType']);
        }
        
        $this->settings->partnerId = $whoami['id'];
        $this->settings->save();
    }

    /**
    * Get the current session ID and type
    */
    public function whoami(){
        $response = Http::withToken($this->bearer())->get($this->baseUrl . '/whoami/v1');
        return $response->json();
    }

    /**
    * Get the current bearer token
    * If the token is expired, it will be regenerated
    */
    private function bearer(){
        if (time() >= $this->settings->token_expires_at) {
            Log::info("Sophos Partner Bearer Token expired, regenerating...");
            $this->authenticate();
        }
        return $this->settings->token;
    }

    /**
    * Get the result from the Sophos Partner API
    * Handles the http request and response with retries

    * @param $method - the http method
    * @param string $url - the url to call
    * @param array $options - the query parameters
    * @param bool $raw - return raw data
    */
    private function send($method, $url, $query, $options = ['raw' => false]){        
        $http = Http::withToken($this->bearer());
        
        if(isset($options['partnerID'])){
            $http = $http->withHeader('X-Partner-ID', $options['partnerID']);
        }
        
        if(isset($options['tenantID'])){
            $http = $http->withHeader('X-Tenant-ID', $options['tenantID']);
        }

        $response = null;
        $attempt = 0;
        $maxAttempts = 10;
        $base = 1000;
        $cap = 30000;
        $backoff = rand(50,200);

        do{
            switch($method){
                case 'GET':
                    $response = $http->get($url, $query);
                    break;
                case 'POST':
                    $response = $http->post($url, $query);
                    break;
                default:
                    throw new Exception("{$method} not supported");
                break;
            }

            if($response->tooManyRequests()){
                $attempt++;
                $backoff = rand(0, min($cap, $base * $attempt));
                Log::info("Sophos Partner API Rate Limit reached, backoff for {$backoff} ms");
                if($backoff > $cap){
                    $backoff = $cap;
                }
                sleep($backoff);
            }else{
                break;
            }
        }while($attempt < $maxAttempts);

        if(!$response->ok()){
            throw new Exception("Sophos Partner API Error: {$response->getStatusCode()}");
        }

        if(isset($options['raw']) && $options['raw'] == true){
            return $response->json();
        }
        
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
     * @param array $options
     * @return result
     */
    
    private function tenantSend($method, $tenant, $url, $options = []){
        $url = $tenant['apiHost'] . $url;      
           
        $query = [];
        if(isset($options['pageing'])){
            $query['pageTotal'] = $options['pageing'];
        }

        if(isset($options['pageByKey'])){
            $query['pageTotal'] = $options['pageByKey'];
        }

        if(isset($options['query'])){
            $query = array_merge($query, $options['query']);
        }

        $preparedOptions = ['tenantID' => $tenant->id];
        
        if(isset($options['raw']) && $options['raw'] == true){
            return $this->send($method, $url, $query, $preparedOptions);
        }
        
        $result = $this->send($method, $url, $query, $preparedOptions);

        $items = collect($result['items']);

        if(isset($options['pageing']) && $options['pageing'] == true){
            $totalPages = $result['pages']['total'];
            for($p = 2; $p <= $totalPages ; $p++){
                $query['page'] = $p;
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }

        if(isset($options['pageByKey']) && $options['pageByKey'] == true){
            $totalPages = $result['pages']['total'];
            for($p = 2; $p <= $totalPages ; $p++){
                $query['pageFromKey'] = $result['pages']['nextKey'];
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }
        return $items;
    }

    public function partnerSend($method = 'GET', $url, $options = []){

        $url = $this->baseUrl . $url; 
        
        $query = [];
        if(isset($options['pageing'])){
            $query['pageTotal'] = $options['pageing'];
        }

        if(isset($options['pageByKey'])){
            $query['pageTotal'] = $options['pageByKey'];
        }

        $preparedOptions = ['partnerID' => $this->settings->partnerId ];

        if(isset($options['raw']) && $options['raw'] == true){
            return $this->send($method, $url, $query, $preparedOptions);
        }
        
        $result = $this->send($method, $url, $query, $preparedOptions);
        
        $items = collect($result['items']);

        if(isset($options['pageing']) && $options['pageing'] == true){
            $totalPages = $result['pages']['total'];
            for($p = 2; $p <= $totalPages ; $p++){
                $query['page'] = $p;
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }

        if(isset($options['pageByKey']) && $options['pageByKey'] == true){
            $totalPages = $result['pages']['total'];
            for($p = 2; $p <= $totalPages ; $p++){
                $query['pageFromKey'] = $result['pages']['nextKey'];
                $result = $this->send($method, $url, $query, $preparedOptions);
                $tItems = collect($result['items']);
                $items = $items->merge($tItems);
            }
        }

        return $items;
    }

    public function partnerGet($url, $options = []){
        return $this->partnerSend('GET', $url, $options);
    }

    public function partnerPost($url, $options = []){
        return $this->partnerSend('POST', $url, $options);
    }

    public function tenantGet(SCTenant $tenant, $url, $options = []){
        return $this->tenantSend('GET',$tenant, $url, $options);
    }

    public function tenantPost(SCTenant $tenant, $url, $options = []){
        return $this->tenantSend('POST', $tenant, $url, $options);
    }


    public function tenants($filter = [ 'IncludeTrail' => false, 'IncludeInactive' => false ]){
        $items = $this->partnerGet('/partner/v1/tenants',[ 
            'pageing' => true,
         ]);
         return $items;
    }

    public function tenant(string $uuid){
        return $this->partnerGet('/partner/v1/tenants/{$uuid}');
    }
    
    public function accountHealthCheck(SCTenant $tenant){
        return $this->tenantGet($tenant, '/account-health-check/v1/health-check', ['raw' => true]);
    }

    public function alerts(SCTenant $tenant, $from){        

        return $this->tenantGet($tenant, '/common/v1/alerts', [
            'pageByKey' => true,
            'query' => [
                'from' => $from ->isoFormat("YYYY-MM-DDTHH:mm:ss.000[Z]"),
                'to' => now()->isoFormat("YYYY-MM-DDTHH:mm:ss.000[Z]")
            ]
        ]);
    }

    public function billingUsage(int $month, int $year){
        return $this->partnerGet('/partner/v1/billing/usage/'.$year.'/'.$month, 
            [ 'pageByKey' => true ]
        );
    }

    public function endpoints(SCTenant $tenant){        
        return $this->tenantGet($tenant, '/endpoint/v1/endpoints', [
            'pageByKey' => true,
        ]);
    }

    public function downloads(SCTenant $tenant){        
        return $this->tenantGet($tenant, '/endpoint/v1/downloads');
    }
    
    public function fakebillingUsage(int $month, int $year){

        $date = now()->setYear($year)->setMonth($month);
        

        $sctenants = SCTenant::all();

        $data = collect();
        foreach( $sctenants as $sct ){
            for($i = 0; $i < rand(1, 15); $i++){
                $data->push([
                    'orderLineItemNumber' => fake()->uuid(),
                    'productGroup' => fake()->word(),
                    'billableQuantity' => fake()->randomNumber(2),
                    'productCode' => fake()->isbn13(),
                    'sku' => fake()->word(),
                    'productDescription' => fake()->word(),
                    'accountId' => $sct->id,
                    'partnerAccountId' => fake()->uuid(),
                    'partnerEdiAccountNumber' => fake()->uuid() ,
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
