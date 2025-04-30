<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\CleanOldShipRealtimePositions;

// Schedule the CleanOldShipRealtimePositions command to run every minute
Schedule::job(new CleanOldShipRealtimePositions)->everyMinute();
