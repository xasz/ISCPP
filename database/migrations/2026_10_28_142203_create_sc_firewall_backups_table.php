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
        Schema::create('sc_firewall_backups', function (Blueprint $table) {
            $table->id();

            // Deliberately no foreign keys: RefreshSCTenants hard deletes tenants, and a
            // configuration backup is most valuable exactly when its firewall is gone.
            $table->uuid('firewallId')->index();
            $table->uuid('tenantId')->index();

            $table->string('status');
            $table->uuid('transactionId')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->text('error')->nullable();
            $table->string('requested_by')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['firewallId', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_firewall_backups');
    }
};
