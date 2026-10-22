<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sc_tenant_healthscore_thresholds', function (Blueprint $table) {
            $table->id();

            // Real foreign key. The existing SC migrations use uuid()->references()->on(),
            // which emits no constraint at all, so it is not copied here.
            $table->foreignUuid('tenantId')->constrained('sc_tenants')->cascadeOnDelete();

            $table->string('score_key');

            // A row is created lazily for every evaluated score. An absent override
            // therefore means the configured default (100), not "unmonitored".
            $table->unsignedTinyInteger('threshold')->default(100);

            // unknown | ok | low
            $table->string('state')->default('unknown');
            $table->unsignedTinyInteger('last_value')->nullable();
            $table->timestamp('last_evaluated_at')->nullable();
            $table->timestamp('last_changed_at')->nullable();

            $table->timestamps();

            $table->unique(['tenantId', 'score_key'], 'sc_tenant_healthscore_threshold_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sc_tenant_healthscore_thresholds');
    }
};
