<?php

namespace App\Services;

use App\Models\Event;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use App\Settings\HaloServiceSettings;

class HaloService
{
    private HaloServiceSettings $settings;

    public function __construct(HaloServiceSettings $settings){
        $this->settings = $settings;
    }

    public function test(){
        $this->authenticate();        
    }

    private function authUrl(){
        return 'https://' . $this->settings->url . '/auth';
    }

    private function authTokenUrl(){
        return $this->authUrl() . '/token';
    }

    private function apiUrl(){
        return 'https://' . $this->settings->url . '/api';
    }

    public function authenticate(){
      
        $client = new Client();
        $response = $client->post($this->authTokenUrl(), [
            'query' => [
                'tenant' => $this->settings->instance
            ],
            'form_params' => [
                'client_id' => $this->settings->clientId,
                'client_secret' => decrypt($this->settings->clientSecret),
                'grant_type' => 'client_credentials',
                'scope' => $this->settings->scope,
            ],
        ]);

        $content = $response->getBody()->getContents();
        $result = json_decode($content);
        if(property_exists($result, 'access_token') == false){
            throw new Exception("Halo PSA Authentification failed");
        }

        $this->settings->token = $result->access_token;
        $this->settings->token_expires_at = time() + $result->expires_in;
        $this->settings->save();
    }

    private function bearer()
    {
        if ($this->settings->token_expires_at == null || time() >= $this->settings->token_expires_at) {
            Event::logInfo("halo", "Halo API Token Expired, Regenerating...");
            $this->authenticate();
        }
        return $this->settings->token;
    }

    public function haloGet(string $endpoint, ?array $body = null)
    {
        $response = Http::withToken($this->bearer())->retry(10, 2000)->get($this->apiUrl().'/'.$endpoint, $body);
        return $response;
    }

    public function haloPost(string $endpoint, $json = null)
    {
        $response = Http::withToken($this->bearer())->retry(10, 2000)->post($this->apiUrl().'/'.$endpoint, $json);
        return $response;
    }

    public function haloGetPaginate(string $endpoint, $params, $item)
    {   
        $pageNumber = 1;
        $pageSize = 50;
        $totalPages = 1;

        $params = collect([
            'pageinate' => true,
            'page_size' => $pageSize,
            'page_no' => $pageNumber
        ])->merge(collect($params))->toArray();

        $items = collect();

        do{
            $response = Http::withToken($this->bearer())
                ->withQueryParameters($params)        
                ->retry(10, 2000)
                ->get($this->apiUrl().'/'.$endpoint);
            if($response->ok()){
                $totalPages = ceil($response->json()['record_count'] / $pageSize);   
                $items = $items->merge(collect($response->json()[$item]));
            }
            $pageNumber++;
        }while($pageNumber < $totalPages);
        
        return $items;
    }
}