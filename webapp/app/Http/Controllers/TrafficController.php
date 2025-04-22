<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TrafficController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('traffic/Index');
    }

    /**
     * Display the heatmap of ship positions.
     */
    public function heatmap(): JsonResponse
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
