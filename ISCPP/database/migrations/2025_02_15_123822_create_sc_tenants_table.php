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
        Schema::create('sc_tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('showAs');
            $table->string('name');
            $table->string('dataGeography')->nullable();
            $table->string('dataRegion')->nullable();
            $table->string('billingType');
            $table->uuid('partnerId')->nullable();
            $table->uuid('organizationId')->nullable();
            $table->string('apiHost')->nullable();
            $table->json('rawData');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_tenants');
    }
};
