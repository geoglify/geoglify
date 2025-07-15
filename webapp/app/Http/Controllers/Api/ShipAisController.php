<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessShipAisPositions;

class ShipAisController extends Controller
{
    /**
     * Receive a list of AIS positions and dispatch for asynchronous processing.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'positions' => 'required|array',
            'positions.*.mmsi' => 'required|integer',
            'positions.*.latitude' => 'required|numeric',
            'positions.*.longitude' => 'required|numeric',
            'positions.*.timestamp' => 'required|date',
            'positions.*.cog' => 'nullable|numeric',
            'positions.*.sog' => 'nullable|numeric',
            'positions.*.heading' => 'nullable|integer',
            'positions.*.nav_status' => 'nullable|integer',
            'positions.*.rot' => 'nullable|numeric',
            'positions.*.repeat' => 'nullable|integer',
            'positions.*.channel' => 'nullable|string',
            'positions.*.utc' => 'nullable|integer',
            'positions.*.smi' => 'nullable|string',
        ]);

        // Dispatch the job to process the AIS positions
        ProcessShipAisPositions::dispatch($data['positions']);

        return response()->json(['status' => 'accepted', 'count' => count($data['positions'])], 202);
    }
}
