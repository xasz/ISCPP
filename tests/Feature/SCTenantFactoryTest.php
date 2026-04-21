<?php

use App\Models\SCTenant;

test('sc tenant factory derives persisted fields from raw data', function () {
    $tenant = SCTenant::factory()->make();

    $rawData = json_decode($tenant->rawData, true);

    expect($rawData)->toBeArray()
        ->and($rawData['id'])->toBe($tenant->id)
        ->and($rawData['showAs'])->toBe($tenant->showAs)
        ->and($rawData['name'])->toBe($tenant->name)
        ->and($rawData['dataGeography'])->toBe($tenant->dataGeography)
        ->and($rawData['dataRegion'])->toBe($tenant->dataRegion)
        ->and($rawData['billingType'])->toBe($tenant->billingType)
        ->and($rawData['apiHost'])->toBe($tenant->apiHost)
        ->and($rawData['partner']['id'])->toBe($tenant->partnerId)
        ->and($rawData['organization']['id'])->toBe($tenant->organizationId)
        ->and($rawData['contact'])->toBeArray()
        ->and($rawData['contact']['firstName'])->not->toBe('')
        ->and($rawData['contact']['lastName'])->not->toBe('')
        ->and($rawData['contact']['email'])->toContain('@')
        ->and($rawData['products'])->each->toHaveKey('code')
        ->and($tenant->haloclient_id)->toBe(-1)
        ->and($tenant->ninjaorg_id)->toBe(-1);
});
