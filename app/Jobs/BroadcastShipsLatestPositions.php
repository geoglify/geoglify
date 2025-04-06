<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\ShipsLatestPositionsUpdated;
use App\Models\ShipLatestPositionView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class BroadcastShipsLatestPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const CACHE_KEY = 'last_broadcasted_ships_positions';
    const CACHE_TTL = 86400; // 24 hours in seconds

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all current positions at once
        $currentPositions = ShipLatestPositionView::all();

        // Get last broadcasted data from Redis cache
        $lastBroadcastedData = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function() {
            return [];
        });

        $changedPositions = $currentPositions->filter(function ($position) use ($lastBroadcastedData) {
            $shipId = $position->id;
            $lastUpdated = $lastBroadcastedData[$shipId]['updated_at'] ?? null;

            // Include if new or updated
            return $lastUpdated === null || $lastUpdated !== $position->updated_at;
        });

        if ($changedPositions->isNotEmpty()) {
            Log::info('Broadcasting changed ships positions', [
                'count' => $changedPositions->count(),
                'changed_ids' => $changedPositions->pluck('id')->toArray()
            ]);

            // Use Redis to publish the event directly for better performance
            Redis::publish(
                'ships-positions-channel', 
                json_encode(new ShipsLatestPositionsUpdated($changedPositions->toArray()))
            );

            // Update cache with TTL
            $this->updateCache($changedPositions, $lastBroadcastedData);
        } else {
            Log::info('No ship positions have changed since last broadcast');
        }
    }

    protected function updateCache($changedPositions, array $lastBroadcastedData): void
    {
        $changedPositions->each(function ($position) use (&$lastBroadcastedData) {
            $lastBroadcastedData[$position->id] = [
                'updated_at' => $position->updated_at,
            ];
        });

        // Store with TTL
        Cache::put(self::CACHE_KEY, $lastBroadcastedData, self::CACHE_TTL);
    }
}
