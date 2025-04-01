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
        Schema::create('webhook_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('sc_alert_id');
            $table->json('payload');
            $table->string('url');
            $table->integer('statusCode');
            $table->text('response');
            $table->timestamps();
        });

        Schema::table('webhook_log', function (Blueprint $table) {
            $table->foreign('sc_alert_id')->references('id')->on('sc_alerts');
        });

        Schema::table('sc_alerts', function (Blueprint $table) {
            $table->string('webhook_sent')->default('unplanned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_log');

        Schema::table('sc_alerts', function (Blueprint $table) {
            $table->dropColumn('webhook_sent');
        });
    }
};
