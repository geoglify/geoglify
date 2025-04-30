<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CargoType;
use App\Models\ShipHistoricalPosition;
use App\Models\ShipRealtimePosition;
use App\Events\ShipsLatestPositionsUpdated;
use App\Models\ShipLatestPositionView;
use App\Models\Ship;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class StoreAisData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $aisData;

    public function __construct(array $aisData)
    {
        $this->aisData = $aisData;
    }

    public function handle()
    {
        if (!is_array($this->aisData) || empty($this->aisData)) {
            Log::warning("Skipping AIS data: Not an array or empty", $this->aisData);
            return;
        }

        $shipsToUpsert = [];
        $realtimePositionsToUpsert = [];
        $historicalPositionsToInsert = [];

        foreach ($this->aisData as $shipData) {
            if (!is_array($shipData)) {
                Log::warning("Skipping invalid ship data: Not an array", $shipData);
                continue;
            }

            if (!isset($shipData['mmsi']) && !isset($shipData['imo'])) {
                Log::warning("Skipping ship data: Missing 'mmsi' and 'imo' keys", $shipData);
                continue;
            }

            // Prepare data for each model
            $ship = $this->prepareShipData($shipData);
            if ($ship) {
                $shipsToUpsert[] = $ship;
            }

            $realtimePosition = $this->prepareRealtimePositionData($shipData);
            if ($realtimePosition) {
                $realtimePositionsToUpsert[] = $realtimePosition;
            }

            $historicalPosition = $this->prepareHistoricalPositionData($shipData);
            if ($historicalPosition) {
                $historicalPositionsToInsert[] = $historicalPosition;
            }
        }

        // Process in chunks to avoid memory issues
        $this->processInChunks($shipsToUpsert, $realtimePositionsToUpsert, $historicalPositionsToInsert);
    }

    protected function processInChunks(array $ships, array $realtimePositions, array $historicalPositions)
    {
        $chunkSize = 500;

        // Process ships
        foreach (array_chunk($ships, $chunkSize) as $chunk) {
            $this->bulkUpsertShips($chunk);
        }

        // Process realtime positions
        foreach (array_chunk($realtimePositions, $chunkSize) as $chunk) {
            $this->bulkUpsertRealtimePositions($chunk);
        }

        // Process historical positions
        foreach (array_chunk($historicalPositions, $chunkSize) as $chunk) {
            $this->bulkInsertHistoricalPositions($chunk);
        }
        
        foreach (array_chunk($ships, $chunkSize) as $chunk) {
            $this->broadcastShips($chunk);
        }
    }
    
    protected function broadcastShips(array $ships)
    {
        if (empty($ships)) {
            return;
        }

        $mmsiList = array_column($ships, 'mmsi');
        $latestPositions = ShipLatestPositionView::whereIn('mmsi', $mmsiList)->get();

        if ($latestPositions->isNotEmpty()) {
            Log::info('Broadcasting ships', ['size' => $latestPositions->count()]);
            broadcast(new ShipsLatestPositionsUpdated($latestPositions->toArray()));
        }
    }

    protected function prepareShipData(array $shipData): ?array
    {
        if (!isset($shipData['mmsi'])) {
            return null;
        }

        $cargoType = $this->getCargoType($shipData);
        $now = Carbon::now();

        // Define all possible fields with defaults
        $data = [
            'name' => $shipData['name'] ?? null,
            'dim_a' => $shipData['dim_a'] ?? null,
            'dim_b' => $shipData['dim_b'] ?? null,
            'dim_c' => $shipData['dim_c'] ?? null,
            'dim_d' => $shipData['dim_d'] ?? null,
            'imo' => $shipData['imo'] ?? null,
            'callsign' => $shipData['callsign'] ?? null,
            'draught' => $shipData['draught'] ?? null,
            'cargo_type_id' => $cargoType?->id ?? null,
            'mmsi' => $shipData['mmsi'],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $this->filterData($data);
    }

    protected function prepareRealtimePositionData(array $shipData): ?array
    {
        if (!isset($shipData['id']) && !isset($shipData['mmsi'])) {
            return null;
        }

        $shipId = $shipData['id'] ?? Ship::where('mmsi', $shipData['mmsi'])->value('id');
        if (!$shipId) {
            return null;
        }

        $now = Carbon::now();

        // Define all possible fields with defaults
        $data = [
            'ship_id' => $shipId,
            'cog' => $shipData['cog'] ?? null,
            'sog' => $shipData['sog'] ?? null,
            'hdg' => $shipData['hdg'] ?? null,
            'last_updated' => $shipData['last_updated'] ?? $now,
            'eta' => $shipData['eta'] ?? null,
            'destination' => $shipData['destination'] ?? null,
            'latitude' => $shipData['latitude'] ?? null,
            'longitude' => $shipData['longitude'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $this->filterData($data);
    }

    protected function prepareHistoricalPositionData(array $shipData): ?array
    {
        if (!isset($shipData['id']) && !isset($shipData['mmsi'])) {
            return null;
        }

        $shipId = $shipData['id'] ?? Ship::where('mmsi', $shipData['mmsi'])->value('id');
        if (!$shipId) {
            return null;
        }

        $now = Carbon::now();

        $data = [
            'ship_id' => $shipId,
            'cog' => $shipData['cog'] ?? null,
            'sog' => $shipData['sog'] ?? null,
            'hdg' => $shipData['hdg'] ?? null,
            'last_updated' => $shipData['last_updated'] ?? $now,
            'eta' => $shipData['eta'] ?? null,
            'destination' => $shipData['destination'] ?? null,
            'latitude' => $shipData['latitude'] ?? null,
            'longitude' => $shipData['longitude'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $this->filterData($data);
    }

    protected function bulkUpsertShips(array $shipsData)
    {
        if (empty($shipsData)) {
            return;
        }

        // Get all possible columns from the first item
        $columns = array_keys($shipsData[0]);

        // Ensure all items have the same columns
        $normalizedData = array_map(function ($item) use ($columns) {
            $normalized = [];
            foreach ($columns as $column) {
                $normalized[$column] = $item[$column] ?? null;
            }
            return $normalized;
        }, $shipsData);

        Ship::upsert(
            $normalizedData,
            ['mmsi'],
            array_diff($columns, ['mmsi', 'created_at'])
        );
    }

    protected function bulkUpsertRealtimePositions(array $positionsData)
    {
        if (empty($positionsData)) {
            return;
        }

        // Get all possible columns from the first item
        $columns = array_keys($positionsData[0]);

        // Ensure all items have the same columns
        $normalizedData = array_map(function ($item) use ($columns) {
            $normalized = [];
            foreach ($columns as $column) {
                $normalized[$column] = $item[$column] ?? null;
            }
            return $normalized;
        }, $positionsData);

        ShipRealtimePosition::upsert(
            $normalizedData,
            ['ship_id'],
            array_diff($columns, ['ship_id', 'created_at'])
        );
    }

    protected function bulkInsertHistoricalPositions(array $positionsData)
    {
        if (empty($positionsData)) {
            return;
        }

        // Get all possible columns from the first item
        $columns = array_keys($positionsData[0]);

        // Ensure all items have the same columns
        $normalizedData = array_map(function ($item) use ($columns) {
            $normalized = [];
            foreach ($columns as $column) {
                $normalized[$column] = $item[$column] ?? null;
            }
            return $normalized;
        }, $positionsData);

        ShipHistoricalPosition::insert($normalizedData);
    }

    protected function getCargoType(array $shipData): ?CargoType
    {
        return isset($shipData['cargo'])
            ? CargoType::where('code', (int) $shipData['cargo'])->first()
            : null;
    }

    protected function filterData(array $data): array
    {
        return array_filter($data, fn($value) => $value !== '' && $value !== null);
    }
}
