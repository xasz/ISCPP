<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Grace period marker for tenant removal.
     *
     * A Sophos partner call that returns an empty list is a 200, not an exception, so
     * deleting every absent tenant immediately would destroy the tenant table on a
     * transient fault. Tenants are marked absent here and only deleted once they have
     * stayed absent, which also means SCTenantRemoved only fires for real offboardings.
     */
    public function up(): void
    {
        Schema::table('sc_tenants', function (Blueprint $table) {
            $table->timestamp('missing_since')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sc_tenants', function (Blueprint $table) {
            $table->dropColumn('missing_since');
        });
    }
};
