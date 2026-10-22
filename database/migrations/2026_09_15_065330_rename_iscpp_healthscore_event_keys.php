<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Old key => [new key, new label].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private array $renames = [
        'healthscore.low' => ['healthscore.threshold.dropped', 'Healthscore Threshold Dropped'],
        'healthscore.ok' => ['healthscore.threshold.recovered', 'Healthscore Threshold Recovered'],
    ];

    /**
     * Rules match on the key and recorded events are filtered by it, so both move with the
     * rename. The envelope inside recorded payloads carries the key as well and is updated
     * so the event log and the stored JSON agree.
     */
    public function up(): void
    {
        foreach ($this->renames as $old => [$new, $label]) {
            $this->rename($old, $new, $label);
        }
    }

    public function down(): void
    {
        $labels = ['healthscore.low' => 'Healthscore Dropped Below Threshold', 'healthscore.ok' => 'Healthscore Recovered'];

        foreach ($this->renames as $old => [$new]) {
            $this->rename($new, $old, $labels[$old]);
        }
    }

    private function rename(string $from, string $to, string $label): void
    {
        DB::table('iscpp_event_rules')->where('event_key', $from)->update(['event_key' => $to]);

        DB::table('iscpp_events')->where('key', $from)->orderBy('id')->chunkById(500, function ($events) use ($to, $label) {
            foreach ($events as $event) {
                $payload = json_decode($event->payload, true) ?: [];
                $payload['event']['key'] = $to;
                $payload['event']['label'] = $label;

                DB::table('iscpp_events')->where('id', $event->id)->update([
                    'key' => $to,
                    'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]);
            }
        });
    }
};
