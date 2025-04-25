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

class BroadcastShipsLatestPositions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Processing only ships with last_updated no more than 30 seconds ago
        $now = now();
        $lastUpdatedThreshold = $now->subSeconds(30);
        $ships = ShipLatestPositionView::where('last_updated', '>=', $lastUpdatedThreshold)
            ->orderBy('last_updated', 'desc')
            ->get();

        // Chunking the data to avoid memory issues
        $chunkSize = 1000;
        $ships->chunk($chunkSize)->each(function ($chunk) {

            Log::info('Broadcasting ships latest positions', ['count' => $chunk->count()]);

            // Broadcasting the chunk of data after mapping
            broadcast(new ShipsLatestPositionsUpdated($chunk->toArray()));
        });
    }
}
