<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function runMoveQueueAndCacheTablesMigration(): void
{
    $migration = require database_path('migrations/2026_10_28_103328_move_queue_and_cache_tables_to_default_connection.php');
    $migration->up();
}

test('creates the queue tables on the default connection when they are missing', function () {
    Schema::dropIfExists('jobs');
    Schema::dropIfExists('job_batches');
    Schema::dropIfExists('failed_jobs');

    runMoveQueueAndCacheTablesMigration();

    expect(Schema::hasTable('jobs'))->toBeTrue()
        ->and(Schema::hasTable('job_batches'))->toBeTrue()
        ->and(Schema::hasTable('failed_jobs'))->toBeTrue();
});

test('recreates cache tables created with the old schema', function () {
    Schema::dropIfExists('cache');
    Schema::create('cache', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->mediumText('value');
        $table->integer('expiration');
    });

    runMoveQueueAndCacheTablesMigration();

    expect(Schema::hasIndex('cache', ['expiration']))->toBeTrue()
        ->and(Schema::hasIndex('cache_locks', ['expiration']))->toBeTrue();
});

test('leaves existing queue rows untouched on fresh installations', function () {
    DB::table('failed_jobs')->insert([
        'uuid' => 'existing-failed-job',
        'connection' => 'database',
        'queue' => 'default',
        'payload' => '{}',
        'exception' => 'Test',
    ]);

    runMoveQueueAndCacheTablesMigration();

    expect(DB::table('failed_jobs')->where('uuid', 'existing-failed-job')->exists())->toBeTrue();
});
