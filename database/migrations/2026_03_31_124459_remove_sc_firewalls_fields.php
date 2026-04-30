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
        Schema::table('sc_firewalls', function (Blueprint $table) {
            $table->dropColumn('serialNumber');
            $table->dropColumn('firmwareVersion');
            $table->dropColumn('clusterMode');
            $table->dropColumn('clusterId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('sc_firewalls', function (Blueprint $table) {
            $table->string('serialNumber')->nullable();
            $table->string('firmwareVersion')->nullable();
            $table->string('clusterMode')->nullable();
            $table->uuid('clusterId')->nullable();
            $table->timestamps();
        });
    }
};
