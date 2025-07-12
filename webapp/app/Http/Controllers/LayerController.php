<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LayerController extends Controller
{
    public function index()
    {
        return Inertia::render('Map/Index', [
            'layers' => $this->getLayers()
        ]);
    }

    public function list(Request $request)
    {
        $layers = $this->getLayers();

        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search);
            $layers = array_filter($layers, function ($layer) use ($search) {
                return str_contains(strtolower($layer['name']), $search) ||
                    str_contains(strtolower($layer['description']), $search);
            });
        }

        return response()->json([
            'items' => array_values($layers),
            'total' => count($layers)
        ]);
    }

    private function getLayers()
    {
        return [
            [
                'id' => 101,
                'name' => 'Real-Time Data Streams',
                'type' => 'realtime',
                'icon' => 'eos-icons:iot',
                'visible' => true,
                'children' => [
                    ['id' => 102, 'name' => 'Vessels', 'description' => 'Live vessel tracking and statuses', 'type' => 'point', 'icon' => 'mdi-ferry', 'visible' => true, 'opacity' => 0.9, 'category' => 'realtime'],
                    ['id' => 103, 'name' => 'Containers', 'description' => 'Container movement and tracking', 'type' => 'point', 'icon' => 'eos-icons:container', 'visible' => true, 'opacity' => 0.9, 'category' => 'realtime'],
                    ['id' => 104, 'name' => 'People', 'description' => 'Worker access and location', 'type' => 'point', 'icon' => 'mdi-account-group', 'visible' => false, 'opacity' => 0.7, 'category' => 'realtime'],
                    ['id' => 105, 'name' => 'Trucks', 'description' => 'Fleet tracking and logistics flow', 'type' => 'point', 'icon' => 'mdi-truck', 'visible' => true, 'opacity' => 0.8, 'category' => 'realtime'],
                    ['id' => 106, 'name' => 'Trains', 'description' => 'Train positions and schedules', 'type' => 'line', 'icon' => 'mdi-train', 'visible' => true, 'opacity' => 0.8, 'category' => 'realtime'],
                    ['id' => 107, 'name' => 'Environmental Sensors', 'description' => 'Air quality, noise, temperature', 'type' => 'point', 'icon' => 'mdi-thermometer', 'visible' => true, 'opacity' => 0.6, 'category' => 'realtime']
                ]
            ],
            [
                'id' => 109,
                'name' => 'Port Infrastructure',
                'type' => 'warehouse',
                'icon' => 'mdi-warehouse',
                'children' => [
                    ['id' => 110, 'name' => 'Docks', 'description' => 'Berthing areas along the quay', 'type' => 'polygon', 'icon' => 'game-icons:harbor-dock', 'visible' => true, 'opacity' => 0.7, 'category' => 'warehouse'],
                    ['id' => 111, 'name' => 'Terminals', 'description' => 'Operational cargo and passenger terminals', 'type' => 'polygon', 'icon' => 'mdi-ship-wheel', 'visible' => true, 'opacity' => 0.8, 'category' => 'warehouse'],
                    ['id' => 112, 'name' => 'Bollards', 'description' => 'Mooring bollards for vessel anchoring', 'type' => 'point', 'icon' => 'game-icons:mooring-bollard', 'visible' => true, 'opacity' => 0.6, 'category' => 'warehouse'],
                    ['id' => 113, 'name' => 'Fenders', 'description' => 'Protective buffers along docks', 'type' => 'point', 'icon' => 'mdi-shield', 'visible' => false, 'opacity' => 0.5, 'category' => 'warehouse'],
                    ['id' => 114, 'name' => 'Cranes', 'description' => 'Container and cargo handling cranes', 'type' => 'point', 'icon' => 'mdi-crane', 'visible' => true, 'opacity' => 0.8, 'category' => 'warehouse'],
                    ['id' => 115, 'name' => 'Warehouses', 'description' => 'Storage facilities and logistics buildings', 'type' => 'polygon', 'icon' => 'mdi-warehouse', 'visible' => true, 'opacity' => 0.6, 'category' => 'warehouse'],
                    ['id' => 116, 'name' => 'Gates and Checkpoints', 'description' => 'Entry and exit control points', 'type' => 'point', 'icon' => 'mdi-gate', 'visible' => true, 'opacity' => 0.8, 'category' => 'warehouse']
                ]
            ],
            [
                'id' => 117,
                'name' => 'Transport and Access',
                'type' => 'road',
                'icon' => 'mdi-road',
                'children' => [
                    ['id' => 118, 'name' => 'Internal Roads', 'description' => 'Truck and service road network', 'type' => 'line', 'icon' => 'mdi-road-variant', 'visible' => true, 'opacity' => 0.7, 'category' => 'road'],
                    ['id' => 119, 'name' => 'Rail Tracks', 'description' => 'Rail infrastructure inside the port', 'type' => 'line', 'icon' => 'mdi-train-track', 'visible' => true, 'opacity' => 0.7, 'category' => 'road'],
                    ['id' => 120, 'name' => 'Parking Zones', 'description' => 'Parking areas for trucks and staff', 'type' => 'polygon', 'icon' => 'mdi-parking', 'visible' => true, 'opacity' => 0.5, 'category' => 'road'],
                    ['id' => 121, 'name' => 'Waiting Areas', 'description' => 'Buffer zones for trucks and cargo units', 'type' => 'polygon', 'icon' => 'mdi-clock-outline', 'visible' => true, 'opacity' => 0.5, 'category' => 'road']
                ]
            ],
            [
                'id' => 122,
                'name' => 'Utility Networks',
                'type' => 'wrench',
                'icon' => 'mdi-wrench',
                'children' => [
                    ['id' => 123, 'name' => 'Water Pipes', 'description' => 'Water distribution network', 'type' => 'line', 'icon' => 'mdi-pipe', 'visible' => false, 'opacity' => 0.5, 'category' => 'wrench'],
                    ['id' => 124, 'name' => 'Electric Grid', 'description' => 'High and low voltage networks', 'type' => 'line', 'icon' => 'mdi-flash', 'visible' => false, 'opacity' => 0.5, 'category' => 'wrench'],
                    ['id' => 125, 'name' => 'Telecom Lines', 'description' => 'Fiber optic and communication infrastructure', 'type' => 'line', 'icon' => 'mdi-access-point-network', 'visible' => false, 'opacity' => 0.5, 'category' => 'wrench'],
                    ['id' => 126, 'name' => 'Power Stations', 'description' => 'Electrical substations and transformers', 'type' => 'point', 'icon' => 'mdi-transmission-tower', 'visible' => true, 'opacity' => 0.6, 'category' => 'wrench']
                ]
            ]
        ];
    }
}
