<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipRequest;
use App\Http\Requests\UpdateShipRequest;
use Inertia\Inertia;
use App\Models\Ship;
use App\Models\ShipHistoricalPosition;
use App\Models\ShipRealtimePosition;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    /**
     * Return the count of ships grouped by 15-second intervals
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function countShips()
    {
        // Define the time interval (15 seconds)
        $interval = 5; // in seconds

        // Query to get real-time ships and accumulated ships in 1-second intervals
        $data = DB::table('ship_realtime_positions as s1')
            ->selectRaw('FLOOR(EXTRACT(EPOCH FROM s1.last_updated) / 5) AS time_key, COUNT(DISTINCT s1.id) AS current_ships')
            ->where('s1.last_updated', '>=', now()->subMinutes(1)) // Filter the last 30 minutes
            ->where('s1.last_updated', '<', now()->subSeconds(10))
            ->groupBy(DB::raw('FLOOR(EXTRACT(EPOCH FROM s1.last_updated) / 5)')) // Group by the same expression
            ->orderBy('time_key')
            ->get();

        // Now calculate accumulated ships by joining with ShipHistoricalPosition
        $formattedData = $data->map(function ($item) use ($interval) {
            return [
                'timestamp' => Carbon::createFromTimestamp($item->time_key * $interval)->format('H:i:s'), // Format the timestamp
                'ships' => $item->current_ships, // Number of current ships in the interval
            ];
        });

        // Return or process the formatted data as needed
        return $formattedData;
    }

    /**
     * Return the count of ships by type
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function topShipTypes()
    {
        $data = [
            ['type' => 'Container Ship', 'count' => 100],
            ['type' => 'Bulk Carrier', 'count' => 80],
            ['type' => 'Tanker', 'count' => 60],
            ['type' => 'General Cargo', 'count' => 40],
            ['type' => 'Ro-Ro', 'count' => 20],
        ];

        return response()->json($data);
    }

    /**
     * Return the count of ships by country
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function countCountries()
    {
        $data = [
            ['country' => 'Panama', 'count' => 100],
            ['country' => 'Liberia', 'count' => 80],
            ['country' => 'Marshall Islands', 'count' => 60],
            ['country' => 'Singapore', 'count' => 40],
            ['country' => 'Hong Kong', 'count' => 20],
        ];

        return response()->json($data);
    }
}
