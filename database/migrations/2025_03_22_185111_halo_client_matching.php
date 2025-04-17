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
        # add halo id to tenant table
        Schema::table('sc_tenants', function (Blueprint $table) {
            $table->integer('haloclient_id')->default(-1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_tenants', function (Blueprint $table) {
            $table->dropColumn('haloclient_id');
        });
    }
};
