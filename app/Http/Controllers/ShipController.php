<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipRequest;
use App\Http\Requests\UpdateShipRequest;
use Inertia\Inertia;
use App\Models\Ship;
use App\Models\ShipHistoricalPosition;
use App\Models\ShipRealtimePosition;
use App\Models\ShipLatestPositionView;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ShipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $sortField = $request->query('sort', 'name');
        $sortDirection = $request->query('direction', 'asc');
        $perPage = $request->query('perPage', 20);

        // Query ships with necessary relationships
        $query = Ship::query()
            ->with(['cargoType.cargoCategory', 'country'])
            ->leftJoin('cargo_types', 'ships.cargo_type_id', '=', 'cargo_types.id')
            ->leftJoin('cargo_categories', 'cargo_types.cargo_category_id', '=', 'cargo_categories.id')
            ->leftJoin('countries', DB::raw('CAST(LEFT(ships.mmsi::TEXT, 3) AS INTEGER)'), '=', 'countries.number');

        // Filter by search term if it exists
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ships.name', 'like', "%$search%")
                    ->orWhere('cargo_types.name', 'like', "%$search%")
                    ->orWhere('cargo_categories.name', 'like', "%$search%")
                    ->orWhere('countries.name', 'like', "%$search%");
            });
        }
        
        //onyl if imo and callsign are not null
        $query->whereNotNull('imo')->whereNotNull('callsign');

        // Sorting (only allow certain fields)
        $allowedSortFields = [
            'name' => 'ships.name',
            'cargo_type_name' => 'cargo_types.name',
            'cargo_category_name' => 'cargo_categories.name',
            'country_name' => 'countries.name',
        ];
        $sortColumn = $allowedSortFields[$sortField] ?? 'ships.name';
        $query->orderBy($sortColumn, $sortDirection);

        // Select necessary columns
        $query->select([
            'ships.id',
            'ships.name',
            'ships.mmsi',
            'ships.imo',
            'ships.callsign',
            'cargo_types.name as cargo_type_name',
            'cargo_categories.name as cargo_category_name',
            'countries.name as country_name',
            'countries.iso_code as country_iso_code',
        ]);

        // Paginate results
        $ships = $query->paginate($perPage);

        // Format the results
        $formattedShips = $ships->getCollection()->map(function ($ship) {
            return [
                'id' => $ship->id,
                'name' => $ship->name,
                'mmsi' => $ship->mmsi,
                'imo' => $ship->imo,
                'callsign' => $ship->callsign,
                'last_updated' => $ship->latestPosition ? $ship->latestPosition->last_updated : null,
                'cargo_type' => ['name' => $ship->cargo_type_name],
                'cargo_category' => ['name' => $ship->cargo_category_name],
                'country' => [
                    'name' => $ship->country_name,
                    'country_iso_code' => $ship->country_iso_code,
                ],
                'status' => $ship->status,
            ];
        });

        // Replace the original collection with the formatted collection
        $ships->setCollection($formattedShips);

        return Inertia::render('ship/Index', [
            'ships' => $ships,
            'filters' => [
                'search' => $search,
                'sort' => $sortField,
                'direction' => $sortDirection,
                'perPage' => $perPage,
            ],
        ]);
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
     * Return the last positions of a ship in the last x seconds.
     * 
     * @param Ship $ship
     * @param int $seconds
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function lastPositions(Ship $ship, $seconds)
    {
        $shipHistoricalPositions = ShipHistoricalPosition::where('ship_id', $ship->id)
            ->whereNotNull('longitude')
            ->whereNotNull('latitude')
            ->where('last_updated', '>=', now()->subSeconds($seconds))
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
            return response()->json(['message' => 'No valid segments found in the last ' . $seconds . ' seconds'], 404);
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $segments
        ]);
    }

    /**
     * Return the count of ships grouped by 15-second intervals.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countShips()
    {
        $interval = 15; // 15-second interval

        $data = DB::table('ship_realtime_positions')
            ->select(
                DB::raw('FLOOR(EXTRACT(EPOCH FROM last_updated) / ' . $interval . ') AS time_key'),
                DB::raw('COUNT(DISTINCT id) AS current_ships')
            )
            ->where('last_updated', '>=', now()->subMinute(5)) // Last 5 minute
            ->groupBy(DB::raw('FLOOR(EXTRACT(EPOCH FROM last_updated) / ' . $interval . ')'))
            ->orderBy('time_key')
            ->get();

        $formattedData = $data->map(fn($item) => [
            'timestamp' => Carbon::createFromTimestamp($item->time_key * $interval)->format('H:i'),
            'ships' => $item->current_ships
        ]);

        return response()->json($formattedData);
    }

    /**
     * Return the count of ships by category (top 5).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function topCategories()
    {
        $data = ShipLatestPositionView::select(
            'cargo_category_name as category',
            'cargo_category_color as color',
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('cargo_category_name')
            ->groupBy('cargo_category_name', 'cargo_category_color')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json($data);
    }

    /**
     * Return the count of ships by country
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function topCountries()
    {
        $data = ShipLatestPositionView::select(
            'country_name as country',
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('country_name')
            ->groupBy('country_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json($data);
    }
}
