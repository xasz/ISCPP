<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sc_tenant_healthscores', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->json('rawData');
            $table->uuid('tenantId')->references('id')->on('sc_tenants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_tenant_healthscores');
    }
};
