<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layer;
use Illuminate\Http\Request;

class LayerController extends Controller
{
    // Return all layers
    public function index()
    {
        return response()->json(Layer::all());
    }

    // Create a new layer
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'geometry_type' => 'nullable|string|in:POINT,LINESTRING,POLYGON,GEOMETRY',
            'is_visible' => 'boolean',
        ]);

        $layer = Layer::create($validated);

        return response()->json($layer, 201);
    }

    // Show a single layer
    public function show($id)
    {
        $layer = Layer::findOrFail($id);
        return response()->json($layer);
    }

    // Update an existing layer
    public function update(Request $request, $id)
    {
        $layer = Layer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'geometry_type' => 'nullable|string|in:POINT,LINESTRING,POLYGON,GEOMETRY',
            'is_visible' => 'boolean',
        ]);

        $layer->update($validated);

        return response()->json($layer);
    }

    // Delete a layer and its features
    public function destroy($id)
    {
        $layer = Layer::findOrFail($id);

        // Delete related features
        foreach ($layer->features as $feature) {
            $feature->save();
            $feature->delete();
        }

        $layer->save();
        $layer->delete();

        return response()->json(null, 204);
    }

    // Return GeoJSON of all features in a layer
    public function geojson($id)
    {
        $layer = Layer::findOrFail($id);
        $features = $layer->features()->get(['geojson']);

        $collection = [
            'type' => 'FeatureCollection',
            'features' => $features->pluck('geojson')->all(),
        ];

        return response()->json($collection);
    }
}
