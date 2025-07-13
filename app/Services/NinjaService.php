<?php

namespace App\Services;

use App\Settings\NinjaServiceSettings;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NinjaService
{
    private NinjaServiceSettings $settings;

    public function __construct(NinjaServiceSettings $settings){
        $this->settings = $settings;
    }

    public function test(){
        $this->authenticate();        
    }

    private function url(){
        return 'https://' . $this->settings->instance . '.ninjarmm.com';
    }
    private function authTokenUrl(){
        return $this->url() . '/oauth/token';
    }

    private function apiUrl(){
        return $this->url() . '/api';
    }

    public function authenticate(){
      
        $client = new Client();
        $response = $client->post($this->authTokenUrl(), [
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
            throw new Exception("NinjaOne Authentification failed");
        }

        $this->settings->token = $result->access_token;
        $this->settings->token_expires_at = time() + $result->expires_in;
        $this->settings->save();
    }

    private function bearer()
    {
        if ($this->settings->token_expires_at == null || time() >= $this->settings->token_expires_at) {
            Event::logInfo("ninja", "NinjaOne API Token Expired, Regenerating...");
            $this->authenticate();
        }
        return $this->settings->token;
    }

    public function ninjaGet(string $endpoint, array $body = null)
    {
        $response = Http::withToken($this->bearer())->retry(10, 2000)->get($this->apiUrl().'/'.$endpoint, $body);
        return $response;
    }

    public function ninjaPost(string $endpoint, $json = null)
    {
        $response = Http::withToken($this->bearer())->retry(10, 2000)->post($this->apiUrl().'/'.$endpoint, $json);
        return $response;
    }

    public function patchOrganizationCustomField(string $organizationId, string $fieldName, string $value)
    {
        $response = Http::withToken($this->bearer())
            ->retry(10, 2000)
            ->patch($this->apiUrl().'/organizations/'.$organizationId.'/custom-fields/', [
                $fieldName => $value,
            ]);
        return $response;
    }

}