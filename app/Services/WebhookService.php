<?php

namespace App\Services;
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
            
            if($settings->basicAuthEnabled){
                $response = $response->withBasicAuth(
                    $settings->basicAuthUsername,
                    $settings->basicAuthPassword
                );
            }

            $response = $response->post($url, $payload);
            
            $alert->webhookLog()->create([
                'payload' => $payload,
                'url' => $url,
                'response' => $response->body(),
                'statusCode' => $response->getStatusCode(),
            ]);
        }catch(\Exception $e){            
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