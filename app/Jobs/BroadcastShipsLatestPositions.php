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
        // Processing ships in chunks of 100
        ShipLatestPositionView::chunk(200, function ($chunk) {
            
            Log::info('Broadcasting ships latest positions', ['count' => $chunk->count()]);
            
            // Broadcasting the chunk of data after mapping
            broadcast(new ShipsLatestPositionsUpdated($chunk->toArray()));
        });
    }
}
