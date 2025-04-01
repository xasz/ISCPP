<?php


namespace App\Services;

use App\Models\SCTenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class APICrendentialService {

    public static function new(): APICrendentialService
    {
        return new self();
    }

    public function saveCredentials(string $target, string $clientid, string $clientsecret): void
    {
        DB::table('api_credentials')->updateOrInsert(['target' => $target], [
            'clientid' => $clientid,
            'clientsecret' => encrypt($clientsecret),
        ]);
    }

    public function updateToken(string $target, string $token, string $refresh_token, Carbon $expires_at): void
    {

        DB::table('api_credentials')->updateOrInsert(['target' => $target], [
            'token' => $token,
            'refresh_token' => $refresh_token,
            'token_expires_at' => $expires_at,
        ]);
    }

    public function getCredentials(string $target): ?object
    {
        return DB::table('api_credentials')->where('target', $target)->first();
    }

 }