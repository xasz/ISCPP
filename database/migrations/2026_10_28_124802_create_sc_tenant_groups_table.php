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
        Schema::create('sc_tenant_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sc_tenant_group_members', function (Blueprint $table) {
            $table->foreignId('sc_tenant_group_id')->constrained('sc_tenant_groups')->cascadeOnDelete();

            // Deliberately no foreign key: RefreshSCTenants hard deletes tenants, and a
            // tenant lost to a transient Sophos API fault must not silently leave its groups.
            // The membership rebinds when the tenant returns.
            $table->uuid('tenantId');

            $table->primary(['sc_tenant_group_id', 'tenantId']);
            $table->index('tenantId');
        });

        Schema::table('iscpp_event_rules', function (Blueprint $table) {
            // Restrict: deleting a group must never turn its rules into "all tenants" rules.
            $table->foreignId('sc_tenant_group_id')->nullable()->after('tenantId')
                ->constrained('sc_tenant_groups')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iscpp_event_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sc_tenant_group_id');
        });

        Schema::dropIfExists('sc_tenant_group_members');
        Schema::dropIfExists('sc_tenant_groups');
    }
};
