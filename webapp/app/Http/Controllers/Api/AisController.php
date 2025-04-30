<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\StoreAisData;
use Illuminate\Support\Facades\DB;

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

        // Dispatch the job to process the AIS data by chunks of 100
        $aisDataChunks = array_chunk($aisData, 100);
        
        foreach ($aisDataChunks as $chunk) {
            // Dispatch the job for each chunk
            StoreAisData::dispatch($chunk);
        }

        return response()->json(['message' => 'Data processing started'], 202);
    }

    /** 
     * Get the heatmap data for the AIS.
     */
    public function heatmap()
    {
        // Get the ship positions from the database and group them by longitude and latitude
        // Using ST_SnapToGrid to create a grid of points and count the number of ships in each grid cell
        // The grid size is set to 0.05 degrees which is approximately 5.5 km at the equator
        $rows = DB::table('ship_historical_positions')->select(DB::raw('
                ST_X(ST_Centroid(ST_SnapToGrid(geom::geometry, 0.05))) AS longitude,
                ST_Y(ST_Centroid(ST_SnapToGrid(geom::geometry, 0.05))) AS latitude,
                COUNT(*) AS weight
            '))
            ->whereNotNull('geom')
            ->groupByRaw('ST_SnapToGrid(geom::geometry, 0.05)')
            ->get();

        // Map the rows to a GeoJSON format
        $features = $rows->map(fn($row) => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$row->longitude, $row->latitude],
            ],
            'properties' => [
                'weight' => $row->weight,
            ],
        ]);

        // Return the response as a JSON object
        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
