<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\ShipsLatestPositionsUpdated;
use App\Models\ShipLatestPositionView;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BroadcastShipsLatestPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lastBroadcasted = Cache::get('last_ships_broadcasted_at');

        $now = now();
        $lastUpdatedThreshold = $now->subSeconds(5);

        // Check if there are any new ship positions to broadcast
        $ships = ShipLatestPositionView::where('position_updated_at', '>=', $lastBroadcasted ?? $lastUpdatedThreshold)
            ->orderBy('position_updated_at', 'desc')
            ->get();

        if ($ships->isEmpty()) {
            Log::info('No new ship positions to broadcast.');
            return;
        }

        Cache::put('last_ships_broadcasted_at', $now);
        $chunkSize = 1000;
        $ships->chunk($chunkSize)->each(function ($chunk) {
            Log::info('Broadcasting chunk', ['size' => $chunk->count()]);
            broadcast(new ShipsLatestPositionsUpdated($chunk->toArray()));
        });
    }
}
