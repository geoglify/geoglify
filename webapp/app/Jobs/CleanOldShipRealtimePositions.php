<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\ShipRealtimePosition;

class CleanOldShipRealtimePositions implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Calculate the threshold time (120 minutes ago)
        $threshold = Carbon::now()->subMinutes(120);

        // Delete all records older than the threshold
        ShipRealtimePosition::where('last_updated', '<=', $threshold)->delete();
    }
}
