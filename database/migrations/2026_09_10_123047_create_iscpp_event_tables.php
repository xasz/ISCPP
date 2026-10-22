<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iscpp_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key');
            $table->string('severity');

            // Deliberately no foreign key: events must outlive the tenant they describe,
            // and RefreshSCTenants hard deletes tenants.
            $table->uuid('tenantId')->nullable();

            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('payload');
            $table->timestamp('occurred_at');

            $table->index(['key', 'occurred_at']);
            $table->index(['tenantId', 'occurred_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('occurred_at');
        });

        /**
         * A rule is one event, one tenant scope, one webhook.
         *
         * Matching and delivery live on one record because a rule always has exactly one
         * webhook. Two rules wanting the same endpoint simply repeat the URL.
         */
        Schema::create('iscpp_event_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('enabled')->default(true);

            // --- matching ---
            $table->string('event_key');

            // Null means every tenant, plus events that have no tenant at all.
            // Deliberately no foreign key: a tenant lost to a transient Sophos API fault
            // must not silently erase the rule that watches it.
            $table->uuid('tenantId')->nullable();

            // --- delivery ---
            $table->text('url');
            $table->string('basic_auth_username')->nullable();
            $table->text('basic_auth_password')->nullable();

            // Circuit breaker: a rule disables itself after repeated terminal failures.
            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->timestamp('disabled_at')->nullable();

            $table->timestamps();

            $table->index('enabled');
            $table->index(['event_key', 'enabled']);
        });

        Schema::create('iscpp_event_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iscpp_event_rule_id')->constrained('iscpp_event_rules')->cascadeOnDelete();
            $table->string('path');
            $table->string('operator');
            $table->json('value')->nullable();

            $table->index('iscpp_event_rule_id');
        });

        Schema::create('iscpp_event_deliveries', function (Blueprint $table) {
            $table->id();

            // Cascades so pruning iscpp_events cleans deliveries up. constrained() does not
            // create an index and Postgres does not auto index FK child columns, so the
            // prune would otherwise sequentially scan this table on every chunk.
            $table->foreignId('iscpp_event_id')->constrained('iscpp_events')->cascadeOnDelete();
            $table->index('iscpp_event_id');

            $table->foreignId('iscpp_event_rule_id')->nullable()->constrained('iscpp_event_rules')->nullOnDelete();

            $table->unsignedInteger('attempt')->default(1);
            $table->string('status')->default('pending');
            $table->integer('http_status')->nullable();
            $table->text('url')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iscpp_event_deliveries');
        Schema::dropIfExists('iscpp_event_rule_conditions');
        Schema::dropIfExists('iscpp_event_rules');
        Schema::dropIfExists('iscpp_events');
    }
};
