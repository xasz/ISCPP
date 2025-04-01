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
        Schema::create('sc_billable', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->uuid('tenantId')->references('id')->on('sc_tenants');
            $table->string('orderLineItemNumber');
            $table->string('productGroup');
            $table->integer('billableQuantity');
            $table->integer('orderedQuantity');
            $table->integer('actualQuantity');
            $table->string('productCode');
            $table->string('sku');
            $table->text('productDescription');
            $table->json('rawData');

            $table->string('sent_to_halo')->default('unplanned')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_billable');
    }
};
