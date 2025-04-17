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
        Schema::create('sc_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('allowedActions');
            $table->string('category');
            $table->string('description');
            $table->string('groupKey');
            $table->uuid('managedAgentID')->nullable();
            $table->string('managedAgentName')->nullable();
            $table->string('managedAgentType')->nullable();
            $table->uuid('personID')->nullable();
            $table->string('personName')->nullable();
            $table->string('product')->nullable();
            $table->dateTime('raisedAt');
            $table->string('severity');
            $table->uuid('tenantId')->references('id')->on('sc_tenants');
            $table->string('type');
            $table->json('rawData');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_alerts');
    }
};
