<?php

namespace App\Services;
use App\Models\SCAlert;
use App\Settings\WebhookSettings;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WebhookService {

    public function runWebhook(SCAlert $alert) : ?Response{

        $settings = app(WebhookSettings::class);
        $url = $settings->url;

        $payload = $settings->payload;
        $payload = $alert->rawData;
        $response = null;
        
        try{
            $response = Http::asJson()->post($url, json_decode($payload));
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