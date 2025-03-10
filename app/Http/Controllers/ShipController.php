<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipRequest;
use App\Http\Requests\UpdateShipRequest;
use Inertia\Inertia;
use App\Models\Ship;
use App\Models\ShipHistoricalPosition;
use App\Models\ShipRealtimePosition;
use Illuminate\Support\Facades\Lang;

class ShipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShipRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Ship $ship)
    {
        $shipRealtimePosition = ShipRealtimePosition::where('ship_id', $ship->id)->latest()->first();

        // Get all translations from the 'ship.php' file
        $translations = Lang::get('ship');

        return Inertia::render('ship/Show', [
            'ship' => $ship,
            'shipRealtimePosition' => $shipRealtimePosition,
            'translations' => $translations,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ship $ship)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShipRequest $request, Ship $ship)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ship $ship)
    {
        //
    }

    /**
     * Return the last positions of a ship 
     * (8 hours, 24 hours, 7 days)
     * 
     * @param Ship $ship
     * @param int $range
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function lastPositions(Ship $ship, $range)
    {
        $shipHistoricalPositions = ShipHistoricalPosition::where('ship_id', $ship->id)
            ->whereNotNull('longitude')
            ->whereNotNull('latitude')
            ->where('last_updated', '>=', now()->subHours($range))
            ->orderBy('last_updated', 'asc')
            ->get();

        if ($shipHistoricalPositions->isEmpty()) {
            return response()->json(['message' => 'Ship not found or no positions in the requested range'], 404);
        }

        $segments = [];
        $previousPosition = null;

        foreach ($shipHistoricalPositions as $position) {

            $color = '#000000';

            // Create new segment
            if ($previousPosition) {
                $segments[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => [$previousPosition['coordinates'], [floatval($position->longitude), floatval($position->latitude)]],
                    ],
                    'properties' => [
                        'cog' => $position->cog,
                        'sog' => $position->sog,
                        'hdg' => $position->hdg,
                        'last_updated' => $position->last_updated,
                        'color' => $color,
                    ],
                ];
            }
            $previousPosition = [
                'coordinates' => [floatval($position->longitude), floatval($position->latitude)],
                'cog' => $position->cog,
                'sog' => $position->sog,
                'hdg' => $position->hdg,
                'last_updated' => $position->last_updated,
                'legend' => 'SOG: ' . $position->sog . ' - Last Updated: ' . $position->last_updated
            ];
        }

        if (empty($segments)) {
            return response()->json(['message' => 'No valid segments found in the last 24 hours'], 404);
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $segments
        ]);
    }
}
