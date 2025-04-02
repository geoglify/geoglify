<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipRequest;
use App\Http\Requests\UpdateShipRequest;
use Inertia\Inertia;
use App\Models\Ship;
use App\Models\ShipHistoricalPosition;
use App\Models\ShipLatestPositionView;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShipController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve filter parameters from the request
        $filters = $request->only(['search', 'sort', 'direction', 'perPage']);

        // Set default values for sorting and pagination
        $sortField = $filters['sort'] ?? 'name';
        $sortDirection = $filters['direction'] ?? 'asc';
        $perPage = $filters['perPage'] ?? 20;

        // Start building the query for fetching ships data
        $query = Ship::query()
            ->leftJoin('cargo_types', 'ships.cargo_type_id', '=', 'cargo_types.id') // Join with cargo_types table
            ->leftJoin('cargo_categories', 'cargo_types.cargo_category_id', '=', 'cargo_categories.id') // Join with cargo_categories table
            ->leftJoin('countries', DB::raw('CAST(LEFT(ships.mmsi::TEXT, 3) AS INTEGER)'), '=', 'countries.number') // Join with countries table based on mmsi
            ->leftJoin('ship_realtime_positions', 'ships.id', '=', 'ship_realtime_positions.ship_id') // Join with ship_realtime_positions table to get the latest position
            ->whereNotNull(['ships.imo', 'ships.callsign']); // Ensure that imo and callsign are not null

        // Apply search filter if provided
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                // Search in multiple fields like mmsi, name, imo, callsign, cargo types, and categories
                $q->where('ships.mmsi', 'ilike', "%{$filters['search']}%")
                    ->orWhere('countries.name', 'ilike', "%{$filters['search']}%")
                    ->orWhere('ships.name', 'ilike', "%{$filters['search']}%")
                    ->orWhere('ships.imo', 'ilike', "%{$filters['search']}%")
                    ->orWhere('ships.callsign', 'ilike', "%{$filters['search']}%")
                    ->orWhere('cargo_types.name', 'ilike', "%{$filters['search']}%")
                    ->orWhere('cargo_categories.name', 'ilike', "%{$filters['search']}%");
            });
        }

        // Define sortable fields, including the status based on the availability of last_updated
        $sortableFields = [
            'status' => DB::raw('CASE WHEN ship_realtime_positions.last_updated IS NOT NULL THEN \'live\' ELSE \'offline\' END'),
            'last_updated' => 'ship_realtime_positions.last_updated',
            'name' => 'ships.name',
            'mmsi' => 'ships.mmsi',
        ];

        // Order by the selected field and direction
        $query->orderBy($sortableFields[$sortField] ?? 'ships.name', $sortDirection);

        // Select the necessary columns, including the dynamically calculated status
        $ships = $query->select([
            'ships.id',
            'ships.name',
            'ships.mmsi',
            'ships.imo',
            'ships.callsign',
            DB::raw('CASE WHEN ship_realtime_positions.last_updated IS NOT NULL THEN \'live\' ELSE \'offline\' END as status'), // Dynamic status calculation
            'ship_realtime_positions.last_updated',
            'cargo_types.name as cargo_type_name',
            'cargo_categories.name as cargo_category_name',
            'countries.name as country_name',
            'countries.iso_code as country_iso_code',
        ])->paginate($perPage); // Paginate the results

        // Map over the ships to add 'last_updated' and merge into the final result
        return Inertia::render('ship/Index', [
            'ships' => $ships->setCollection($ships->getCollection()->map(fn($ship) => array_merge($ship->toArray(), [
                'last_updated' => $ship->last_updated, // Include last_updated in the final data
            ]))),
            'filters' => $filters, // Return the filters used for the query
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
        $status = $this->getShipStatus($ship);

        // Add cargo type and category names to the ship object
        $this->addCargoInfo($ship);

        // Get all translations from the 'ship.php' file
        $translations = Lang::get('ship');

        return Inertia::render('ship/Show', [
            'ship' => $ship,
            'lastKnownPosition' => $ship->lastKnownPosition,
            'translations' => $translations,
            'status' => $status,
        ]);
    }

    /**
     * Get the ship's status based on its real-time position.
     *
     * @param Ship $ship
     * @return string
     */
    private function getShipStatus(Ship $ship): string
    {
        $shipRealtimePosition = $ship->realtimePosition()->latest()->first();

        // If no real-time position, use the latest historical position
        if (!$shipRealtimePosition) {
            $shipRealtimePosition = $ship->historicalPositions()
                ->whereNotNull('longitude')
                ->whereNotNull('latitude')
                ->latest()
                ->first();
        }

        return $shipRealtimePosition ? 'Offline' : 'Online';
    }

    /**
     * Add cargo type and category names to the ship object.
     *
     * @param Ship $ship
     */
    private function addCargoInfo(Ship $ship): void
    {
        $ship->cargo_type_name = $ship->cargoType ? $ship->cargoType->name : 'Unknown';
        $ship->cargo_category_name = $ship->cargoType && $ship->cargoType->cargoCategory
            ? $ship->cargoType->cargoCategory->name
            : 'Unknown';
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
    public function lastPositions(Ship $ship, Request $request)
    {
        // Get start and end dates from the request
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        
        // Validate the date format
        $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
        $endDate = Carbon::createFromFormat('Y-m-d', $endDate);
        
        // Check if the dates are valid
        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Invalid date format'], 400);
        }
        
        // 
        $shipHistoricalPositions = $this->getShipHistoricalPositions($ship, $startDate, $endDate);

        if ($shipHistoricalPositions->isEmpty()) {
            return response()->json(['message' => 'Ship not found or no positions in the requested range'], 404);
        }

        $segments = $this->createSegments($shipHistoricalPositions);

        if (empty($segments)) {
            return response()->json(['message' => 'No valid segments found in the last ' . $seconds . ' seconds'], 404);
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $segments
        ]);
    }

    /**
     * Get the historical positions of the ship within the last x seconds.
     *
     * @param Ship $ship
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getShipHistoricalPositions(Ship $ship, $startDate, $endDate)
    {
        return ShipHistoricalPosition::where('ship_id', $ship->id)
            ->whereNotNull('longitude')
            ->whereNotNull('latitude')
            ->whereBetween('last_updated', [$startDate, $endDate])
            ->orderBy('last_updated', 'asc')
            ->get();
    }

    /**
     * Create segments from the historical positions.
     *
     * @param \Illuminate\Support\Collection $shipHistoricalPositions
     * @return array
     */
    private function createSegments($shipHistoricalPositions)
    {
        $segments = [];
        $previousPosition = null;

        foreach ($shipHistoricalPositions as $position) {
            $color = '#000000';

            // Create new segment if there's a previous position
            if ($previousPosition) {
                $segments[] = $this->createSegment($previousPosition, $position, $color);
            }

            $previousPosition = $this->createPositionData($position);
        }

        return $segments;
    }

    /**
     * Create a segment between two positions.
     *
     * @param array $previousPosition
     * @param object $position
     * @param string $color
     * @return array
     */
    private function createSegment($previousPosition, $position, $color)
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => [
                    $previousPosition['coordinates'],
                    [floatval($position->longitude), floatval($position->latitude)]
                ],
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

    /**
     * Create position data for a ship's historical position.
     *
     * @param object $position
     * @return array
     */
    private function createPositionData($position)
    {
        return [
            'coordinates' => [floatval($position->longitude), floatval($position->latitude)],
            'cog' => $position->cog,
            'sog' => $position->sog,
            'hdg' => $position->hdg,
            'last_updated' => $position->last_updated,
            'legend' => 'SOG: ' . $position->sog . ' - Last Updated: ' . $position->last_updated,
        ];
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
