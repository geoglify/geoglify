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
                // Validate required fields - MMSI é sempre obrigatório
                if (!isset($data['mmsi'])) {
                    continue;
                }

                // Busca ou cria o navio
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

                // Se tem coordenadas, cria posição
                if (isset($data['latitude']) && isset($data['longitude'])) {
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
                        'destination' => $data['destination'] ?? null,
                        'draught' => $data['draught'] ?? null,
                        'timestamp' => $data['timestamp'] ?? now(),
                    ]);
                }

                // Atualiza informações do navio se tem dados relevantes
                $this->updateShipInfo($ship, $data);

                // Guarda snapshot completo de toda a mensagem recebida
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
     * Update ship information if relevant data is present
     */
    private function updateShipInfo(Ship $ship, array $data)
    {
        $updateData = [];

        // Campos que podem ser atualizados no ship
        $shipFields = ['name', 'imo', 'call_sign', 'ais_type', 'dim_a', 'dim_b', 'dim_c', 'dim_d', 'cargo', 'length', 'width'];
        
        foreach ($shipFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                $updateData[$field] = $data[$field];
            }
        }

        // Atualiza o ship se há dados para atualizar
        if (!empty($updateData)) {
            $ship->update($updateData);
        }
    }

    /**
     * Create a complete snapshot of the AIS message
     */
    private function createShipSnapshot(Ship $ship, array $data)
    {
        // Remove campos que não devem ir para o snapshot (duplicados)
        $snapshotData = $data;
        unset($snapshotData['mmsi']); // já está no campo separado
        
        ShipSnapshot::create([
            'ship_id' => $ship->id,
            'mmsi' => $data['mmsi'],
            'snapshot_time' => $data['timestamp'] ?? now(),
            'properties' => $snapshotData, // Guarda TODA a mensagem original
        ]);
    }
}
