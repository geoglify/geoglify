<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{

    // Return all features
    public function index()
    {
        return response()->json(Feature::all());
    }

    // Create a new feature
    public function store(Request $request)
    {
        $validated = $request->validate([
            'layer_id' => 'nullable|exists:layers,id',
            'geometry' => 'required',
            'properties' => 'nullable|array',
        ]);

        $feature = new Feature();
        $feature->layer_id = $validated['layer_id'] ?? null;
        $feature->geometry = $validated['geometry'];
        $feature->properties = $validated['properties'] ?? [];

        $feature->save();

        return response()->json($feature, 201);
    }

    // Show a single feature
    public function show($id)
    {
        $feature = Feature::findOrFail($id);
        return response()->json($feature);
    }

    // Update an existing feature
    public function update(Request $request, $id)
    {
        $feature = Feature::findOrFail($id);

        $validated = $request->validate([
            'layer_id' => 'nullable|exists:layers,id',
            'geometry' => 'sometimes|required',
            'properties' => 'nullable|array',
        ]);

        $feature->fill($validated);
        $feature->save();

        return response()->json($feature);
    }

    // Delete a feature
    public function destroy($id)
    {
        $feature = Feature::findOrFail($id);
        $feature->save();
        $feature->delete();

        return response()->json(null, 204);
    }

    // Return the feature as GeoJSON
    public function geojson($id)
    {
        $feature = Feature::findOrFail($id);
        return response()->json($feature->geojson);
    }
}
