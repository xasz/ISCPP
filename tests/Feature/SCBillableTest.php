<?php

use App\Models\User;
use App\Models\SCBillable;
use App\Models\SCTenant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page for scbillables', function () {
    $response = $this->get('/scbillables');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scbillables index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/scbillables');
    $response->assertStatus(200);
});
