<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\CleanOldShipRealtimePositions;
use App\Jobs\BroadcastShipsLatestPositions;

// Schedule the CleanOldShipRealtimePositions command to run every minute
Schedule::job(new CleanOldShipRealtimePositions)->everyMinute();

// Schedule the BroadcastShipsLatestPositions command to run every 5 seconds
Schedule::job(new BroadcastShipsLatestPositions)->everyFiveSeconds();
