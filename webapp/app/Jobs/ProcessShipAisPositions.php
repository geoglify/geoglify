<?php

namespace App\Jobs;

use App\Models\Ship;
use App\Models\ShipPosition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessShipAisPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $positions;

    /**
     * Create a new job instance.
     */
    public function __construct(array $positions)
    {
        $this->positions = $positions;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        foreach ($this->positions as $data) {
            // Create or update the ship
            $ship = Ship::updateOrCreate(
                ['mmsi' => $data['mmsi']],
                [
                    'imo' => $data['imo'] ?? null,
                    'name' => $data['name'] ?? 'Unknown',
                    'call_sign' => $data['call_sign'] ?? null,
                    'ship_type' => $data['ship_type'] ?? null,
                    'dim_a' => $data['dim_a'] ?? null,
                    'dim_b' => $data['dim_b'] ?? null,
                    'dim_c' => $data['dim_c'] ?? null,
                    'dim_d' => $data['dim_d'] ?? null,
                    'properties' => $data['properties'] ?? [],
                ]
            );

            // Create ship position record
            ShipPosition::create([
                'ship_id' => $ship->id,
                'mmsi' => $data['mmsi'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'cog' => $data['cog'] ?? null,
                'sog' => $data['sog'] ?? null,
                'heading' => $data['heading'] ?? null,
                'nav_status' => $data['nav_status'] ?? null,
                'timestamp' => $data['timestamp'],
                'geometry' => null, // Pode ser calculado depois
                'geojson' => null, // Pode ser calculado depois
                'properties' => $data['properties'] ?? [],
                'created_by' => null,
                'updated_by' => null,
            ]);
        }
    }
}
