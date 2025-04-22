<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\StoreAisData;

class AisController extends Controller
{
    /** 
     * Store the AIS data for multiple ships.
     */
    public function store(Request $request)
    {
        // Get JSON data sent by Axios
        $aisData = $request->all();

        // Skip if the data is not an array or is empty
        if (!is_array($aisData) || empty($aisData)) {
            return response()->json(['message' => 'No data to process'], 400);
        }

        // Dispatch the job to process the AIS data
        StoreAisData::dispatch($aisData);

        return response()->json(['message' => 'Data processing started'], 202);
    }
}
