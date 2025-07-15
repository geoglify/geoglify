<?php

namespace App\Jobs;

use App\Models\Ship;
use App\Models\ShipPosition;
use App\Models\ShipSnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessShipAis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $messages;
    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        foreach ($this->messages as $data) {
            try {

                // If MMSI is not set, skip processing this message
                if (!isset($data['mmsi'])) {
                    continue;
                }

                // Create or replace the ship record
                $ship = Ship::firstOrCreate(
                    ['mmsi' => $data['mmsi']],
                    [
                        'name' => $data['name'] ?? 'Unknown',
                        'imo' => $data['imo'] ?? null,
                        'call_sign' => $data['call_sign'] ?? null,
                        'ais_type' => $data['ais_type'] ?? null,
                        'dim_a' => $data['dim_a'] ?? null,
                        'dim_b' => $data['dim_b'] ?? null,
                        'dim_c' => $data['dim_c'] ?? null,
                        'dim_d' => $data['dim_d'] ?? null,
                        'cargo' => $data['cargo'] ?? null,
                        'length' => $data['length'] ?? null,
                        'width' => $data['width'] ?? null,
                    ]
                );

                ShipPosition::create([
                    'ship_id' => $ship->id,
                    'mmsi' => $data['mmsi'],
                    'imo' => $data['imo'] ?? null,
                    'name' => $data['name'] ?? 'Unknown',
                    'call_sign' => $data['call_sign'] ?? null,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'cog' => $data['cog'] ?? null,
                    'sog' => $data['sog'] ?? null,
                    'heading' => $data['heading'] ?? null,
                    'nav_status' => $data['nav_status'] ?? null,
                    'rot' => $data['rot'] ?? null,
                    'repeat' => $data['repeat'] ?? null,
                    'channel' => $data['channel'] ?? null,
                    'utc' => $data['utc'] ?? null,
                    'smi' => $data['smi'] ?? null,
                    'destination' => $data['destination'] ?? null,
                    'draught' => $data['draught'] ?? null,
                    'ais_type' => $data['ais_type'] ?? null,
                    'dim_a' => $data['dim_a'] ?? null,
                    'dim_b' => $data['dim_b'] ?? null,
                    'dim_c' => $data['dim_c'] ?? null,
                    'dim_d' => $data['dim_d'] ?? null,
                    'cargo' => $data['cargo'] ?? null,
                    'length' => $data['length'] ?? null,
                    'width' => $data['width'] ?? null,
                    'timestamp' => $data['timestamp'] ?? now(),
                ]);

                // Save ship snapshot with all relevant data
                $this->createShipSnapshot($ship, $data);

            } catch (\Exception $e) {
                Log::error('Error processing AIS message', [
                    'mmsi' => $data['mmsi'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
    }

    /**
     * Create a complete snapshot of the AIS message
     */
    private function createShipSnapshot(Ship $ship, array $data)
    {
        // Prepare snapshot data, excluding MMSI since it's already in the ship record
        $snapshotData = $data;
        unset($snapshotData['mmsi']); // já está no campo separado

        ShipSnapshot::create([
            'ship_id' => $ship->id,
            'mmsi' => $data['mmsi'],
            'snapshot_time' => $data['timestamp'] ?? now(),
            'properties' => $snapshotData,
        ]);
    }
}
