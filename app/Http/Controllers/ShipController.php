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
        $filters = $request->only(['search', 'sort', 'direction', 'perPage']);
        $sortField = $filters['sort'] ?? 'name';
        $sortDirection = $filters['direction'] ?? 'asc';
        $perPage = $filters['perPage'] ?? 20;

        $query = Ship::query()
            ->with(['cargoType.cargoCategory', 'realtimePosition'])
            ->leftJoin('cargo_types', 'ships.cargo_type_id', '=', 'cargo_types.id')
            ->leftJoin('cargo_categories', 'cargo_types.cargo_category_id', '=', 'cargo_categories.id')
            ->leftJoin('countries', DB::raw('CAST(LEFT(ships.mmsi::TEXT, 3) AS INTEGER)'), '=', 'countries.number')
            ->leftJoin('ship_realtime_positions', 'ships.id', '=', 'ship_realtime_positions.ship_id')
            ->whereNotNull(['ships.imo', 'ships.callsign']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ships.mmsi', 'ilike', "%{$filters['search']}%")
                    ->orWhere('countries.name', 'ilike', "%{$filters['search']}%")
                    ->orWhere('ships.name', 'ilike', "%{$filters['search']}%")
                    ->orWhere('ships.imo', 'ilike', "%{$filters['search']}%")
                    ->orWhere('ships.callsign', 'ilike', "%{$filters['search']}%")
                    ->orWhere('cargo_types.name', 'ilike', "%{$filters['search']}%")
                    ->orWhere('cargo_categories.name', 'ilike', "%{$filters['search']}%");
            });
        }

        $sortableFields = [
            'status' => DB::raw('CASE WHEN ship_realtime_positions.last_updated IS NOT NULL THEN \'live\' ELSE \'offline\' END'),
            'last_updated' => 'ship_realtime_positions.last_updated',
            'name' => 'ships.name',
            'mmsi' => 'ships.mmsi',
        ];

        $query->orderBy($sortableFields[$sortField] ?? 'ships.name', $sortDirection);

        $ships = $query->select([
            'ships.id',
            'ships.name',
            'ships.mmsi',
            'ships.imo',
            'ships.callsign',
            DB::raw('CASE WHEN ship_realtime_positions.last_updated IS NOT NULL THEN \'live\' ELSE \'offline\' END as status'),
            'ship_realtime_positions.last_updated',
            'cargo_types.name as cargo_type_name',
            'cargo_categories.name as cargo_category_name',
            'countries.name as country_name',
            'countries.iso_code as country_iso_code',
        ])->paginate($perPage);

        return Inertia::render('ship/Index', [
            'ships' => $ships,
            'filters' => $filters,
        ]);
    }

    public function show(Ship $ship)
    {
        $status = $this->getShipStatus($ship);
        $this->addCargoInfo($ship);
        $translations = Lang::get('ship');

        return Inertia::render('ship/Show', [
            'ship' => $ship,
            'lastKnownPosition' => $ship->lastKnownPosition,
            'translations' => $translations,
            'status' => $status,
        ]);
    }

    private function getShipStatus(Ship $ship): string
    {
        $shipRealtimePosition = $ship->realtimePosition()->latest()->first();

        if (!$shipRealtimePosition) {
            $shipRealtimePosition = $ship->historicalPositions()
                ->whereNotNull('longitude')
                ->whereNotNull('latitude')
                ->latest()
                ->first();
        }

        return $shipRealtimePosition ? 'Online' : 'Offline';
    }

    private function addCargoInfo(Ship $ship): void
    {
        $ship->cargo_type_name = $ship->cargoType ? $ship->cargoType->name : 'Unknown';
        $ship->cargo_category_name = $ship->cargoType && $ship->cargoType->cargoCategory
            ? $ship->cargoType->cargoCategory->name
            : 'Unknown';
    }

    public function lastPositions(Ship $ship, Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $mapType = $request->input('mapType', 'lines');

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format'], 400);
        }

        $shipHistoricalPositions = $this->getShipHistoricalPositions($ship, $startDate, $endDate);

        if ($shipHistoricalPositions->isEmpty()) {
            return response()->json(['message' => 'No positions found in the requested range'], 404);
        }

        if ($mapType === 'lines') {
            $segments = $this->createSegments($shipHistoricalPositions);
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $segments
            ]);
        }

        if ($mapType === 'grid') {
            $grid = $this->createGrid($shipHistoricalPositions, $ship->id);
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $grid
            ]);
        }

        return response()->json(['message' => 'Invalid map type'], 400);
    }

    private function getShipHistoricalPositions(Ship $ship, $startDate, $endDate)
    {
        return ShipHistoricalPosition::where('ship_id', $ship->id)
            ->whereNotNull('longitude')
            ->whereNotNull('latitude')
            ->whereBetween('last_updated', [$startDate, $endDate])
            ->orderBy('last_updated', 'asc')
            ->get();
    }

    private function createSegments($shipHistoricalPositions)
    {
        $segments = [];
        $previousPosition = null;

        foreach ($shipHistoricalPositions as $position) {
            if ($previousPosition) {
                $segments[] = $this->createSegment($previousPosition, $position, '#000000');
            }
            $previousPosition = $this->createPositionData($position);
        }

        return $segments;
    }

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

    private function createGrid($shipHistoricalPositions, $shipId)
    {
        if ($shipHistoricalPositions->isEmpty()) {
            return [];
        }

        // Get the min and max coordinates
        $minLon = $shipHistoricalPositions->min('longitude');
        $minLat = $shipHistoricalPositions->min('latitude');
        $maxLon = $shipHistoricalPositions->max('longitude');
        $maxLat = $shipHistoricalPositions->max('latitude');

        $grid = DB::select("
        WITH filtered_points AS (
            SELECT ST_SetSRID(ST_MakePoint(longitude, latitude), 4326) AS geom
            FROM ship_historical_positions
            WHERE id IN (" . $shipHistoricalPositions->pluck('id')->implode(',') . ")
        ),
        hex_grid AS (
            SELECT 
                hex.geom,
                (SELECT COUNT(*) 
                 FROM filtered_points 
                 WHERE ST_Intersects(hex.geom, filtered_points.geom)
                ) AS point_count
            FROM 
                ST_HexagonGrid(
                    0.01,  -- Tamanho do hexágono em graus
                    ST_SetSRID(ST_MakeEnvelope(?, ?, ?, ?, 4326), 4326)
                ) AS hex
            WHERE 
                ST_Intersects(hex.geom, ST_SetSRID(ST_MakeEnvelope(?, ?, ?, ?, 4326), 4326))
        )
        SELECT 
            ST_AsGeoJSON(geom) AS geometry,
            point_count
        FROM 
            hex_grid
        WHERE 
            point_count > 0
    ", [$minLon, $minLat, $maxLon, $maxLat, $minLon, $minLat, $maxLon, $maxLat]);

    
        // Calculate color opacity based on point count
        foreach ($grid as $hexagon) {
            $opacity = min(1, $hexagon->point_count / 10); // Adjust the divisor to control opacity
            $hexagon->color = sprintf('rgba(0, 0, 0, %s)', $opacity);
        }
    
        return array_map(function ($hexagon) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($hexagon->geometry),
                'properties' => [
                    'color' => $hexagon->color,
                    'count' => $hexagon->point_count,
                ],
            ];
        }, $grid);
    }

    public function countShips()
    {
        $interval = 15;
        $data = DB::table('ship_realtime_positions')
            ->select(
                DB::raw('FLOOR(EXTRACT(EPOCH FROM last_updated) / ' . $interval . ') AS time_key'),
                DB::raw('COUNT(DISTINCT id) AS current_ships')
            )
            ->where('last_updated', '>=', now()->subMinutes(5))
            ->groupBy('time_key')
            ->orderBy('time_key')
            ->get();

        return response()->json($data->map(fn($item) => [
            'timestamp' => Carbon::createFromTimestamp($item->time_key * $interval)->format('H:i'),
            'ships' => $item->current_ships
        ]));
    }

    public function topCategories()
    {
        return response()->json(
            ShipLatestPositionView::select(
                'cargo_category_name as category',
                'cargo_category_color as color',
                DB::raw('COUNT(*) as count')
            )
                ->whereNotNull('cargo_category_name')
                ->groupBy('cargo_category_name', 'cargo_category_color')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
        );
    }

    public function topCountries()
    {
        return response()->json(
            ShipLatestPositionView::select(
                'country_name as country',
                DB::raw('COUNT(*) as count')
            )
                ->whereNotNull('country_name')
                ->groupBy('country_name')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
        );
    }
}
