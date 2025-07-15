<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessShipAis;

class ShipAisController extends Controller
{
    /**
     * Receive a list of AIS messages and dispatch for asynchronous processing.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'messages' => 'required|array',
            'messages.*.mmsi' => 'required|integer',
            'messages.*.timestamp' => 'required|date',
            'messages.*.latitude' => 'nullable|numeric',
            'messages.*.longitude' => 'nullable|numeric',
            'messages.*.cog' => 'nullable|numeric',
            'messages.*.sog' => 'nullable|numeric',
            'messages.*.heading' => 'nullable|integer',
            'messages.*.nav_status' => 'nullable|integer',
            'messages.*.rot' => 'nullable|numeric',
            'messages.*.repeat' => 'nullable|integer',
            'messages.*.channel' => 'nullable|string',
            'messages.*.utc' => 'nullable|integer',
            'messages.*.smi' => 'nullable|string',
            'messages.*.name' => 'nullable|string',
            'messages.*.imo' => 'nullable|integer',
            'messages.*.call_sign' => 'nullable|string',
            'messages.*.ais_type' => 'nullable|integer',
            'messages.*.destination' => 'nullable|string',
            'messages.*.draught' => 'nullable|numeric',
            'messages.*.cargo' => 'nullable|integer',
            'messages.*.dim_a' => 'nullable|integer',
            'messages.*.dim_b' => 'nullable|integer',
            'messages.*.dim_c' => 'nullable|integer',
            'messages.*.dim_d' => 'nullable|integer',
            'messages.*.length' => 'nullable|numeric',
            'messages.*.width' => 'nullable|numeric',
        ]);

        // Dispatch the job to process the AIS messages
        ProcessShipAis::dispatch($data['messages']);

        return response()->json(['status' => 'accepted', 'count' => count($data['messages'])], 202);
    }
}
