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
        Schema::table('sc_endpoints', function (Blueprint $table) {
            $table->json('rawData')->nullable();
            $table->string('healthStatus');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_endpoints', function (Blueprint $table) {
            $table->dropColumn('rawData');
            $table->dropColumn('healthStatus');
        });
    }
};
