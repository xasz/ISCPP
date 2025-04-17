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
        Schema::create('sc_firewalls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenantId')->references('id')->on('sc_tenants');
            $table->string('serialNumber')->nullable();
            $table->string('hostname')->nullable();
            $table->string('firmwareVersion')->nullable();
            $table->string('clusterMode')->nullable();
            $table->uuid('clusterId')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_firewalls');
    }
};
