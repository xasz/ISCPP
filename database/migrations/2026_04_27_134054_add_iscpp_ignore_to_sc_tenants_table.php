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
        Schema::table('sc_tenants', function (Blueprint $table) {
            $table->boolean('iscpp_ignore')->default(false)->after('apiHost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_tenants', function (Blueprint $table) {
            $table->dropColumn('iscpp_ignore');
        });
    }
};
