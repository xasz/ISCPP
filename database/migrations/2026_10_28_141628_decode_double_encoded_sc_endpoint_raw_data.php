<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * RefreshSCEndpoints used to json_encode() the endpoint before handing it to the array
 * cast, so rawData was stored as a JSON string containing JSON. Every data_get() on it
 * returned the default. This unwraps the stored rows once; the job now stores arrays.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('sc_endpoints')->select(['id', 'rawData'])->orderBy('id')->chunk(500, function ($rows) {
            foreach ($rows as $row) {
                $decoded = json_decode((string) $row->rawData, true);

                if (! is_string($decoded)) {
                    continue;
                }

                $inner = json_decode($decoded, true);

                if (! is_array($inner)) {
                    continue;
                }

                DB::table('sc_endpoints')->where('id', $row->id)->update(['rawData' => json_encode($inner)]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
