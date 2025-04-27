<?php

use App\Models\User;
use App\Models\WebhookLog;

test('guests are redirected to the login page for webhook logs', function () {
    $response = $this->get('/webhookLogs');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the webhook logs page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/webhookLogs');
    $response->assertStatus(200);
});