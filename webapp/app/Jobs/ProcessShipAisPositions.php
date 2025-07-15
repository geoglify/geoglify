<?php

namespace App\Jobs;

use App\Models\Ship;
use App\Models\ShipPosition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessShipAisPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $positions;
    public $tries = 3;
    public $timeout = 120;

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
            try {
                // Validate required fields - agora exige coordenadas
                if (!isset($data['mmsi']) || !isset($data['latitude']) || !isset($data['longitude'])) {
                    continue;
                }

                // Busca ou cria o navio (apenas MMSI para posições)
                $ship = Ship::firstOrCreate(
                    ['mmsi' => $data['mmsi']],
                    ['name' => 'Unknown']
                );

                // Create ship position (apenas dados de posição)
                ShipPosition::create([
                    'ship_id' => $ship->id,
                    'mmsi' => $data['mmsi'],
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
                    'timestamp' => $data['timestamp'] ?? now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Error processing AIS position', [
                    'mmsi' => $data['mmsi'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
    }
}
