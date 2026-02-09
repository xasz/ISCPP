<?php

namespace App\Services;

use App\Models\Event;
use App\Models\SCAlert;
use App\Settings\WebhookSettings;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WebhookService {

    public static function generatedPayload(SCAlert $alert){
        $settings = app(WebhookSettings::class);
        $payload = json_decode($alert->rawData, true); // Ensure associative array

        if ($settings->enableHaloData && $alert->SCTenant) {
            $payload['halo'] = [
            'client_id' => $alert->SCTenant->haloclient_id,
            ];
        }
        return $payload;
    }

    public function runWebhook(SCAlert $alert) : ?Response{

        $settings = app(WebhookSettings::class);
        $url = $settings->url;
        $payload = $this->generatedPayload($alert);

        $response = null;
        try{
            $response = Http::asJson();
            
            $authEnabled = false;

            if($settings->basicAuthEnabled){
                $response = $response->withBasicAuth(
                    $settings->basicAuthUsername,
                    decrypt($settings->basicAuthPassword)
                );
                $authEnabled = true;
            }

            Event::logDebug('webhook', 'Sending webhook', ['payload' => $payload, 'url' => $url, 'authEnabled' => $authEnabled]); 
            $response = $response->post($url, $payload);

            Event::logDebug('webhook', 'Webhook response received', ['status' => $response->getStatusCode(), 'body' => $response->body()]);

            $alert->webhookLog()->create([
                'payload' => $payload,
                'url' => $url,
                'response' => $response->body(),
                'statusCode' => $response->getStatusCode(),
            ]);
        }catch(\Exception $e){ 
            Event::logDebug('webhook', 'Exception occurred while sending webhook', ['error' => $e->getMessage(), 'alertId' => $alert->id]);           
            $alert->webhookLog()->create([
                'payload' => $payload,
                'url' => $url,
                'response' => $e->getMessage(),
                'statusCode' => -1,
            ]);
        }
        return $response;
    }
}