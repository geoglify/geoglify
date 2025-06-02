<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LayerController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $layers = Layer::withCount('features')->get();
            return response()->json([
                'success' => true,
                'data' => $layers,
                'total' => $layers->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching layers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:layers,name',
                'description' => 'nullable|string|max:1000',
                'geometry_type' => 'nullable|string|in:POINT,LINESTRING,POLYGON,MULTIPOINT,MULTILINESTRING,MULTIPOLYGON,GEOMETRY',
                'is_visible' => 'boolean',
                'style' => 'nullable|array',
            ]);

            $validated['is_visible'] = $validated['is_visible'] ?? true;

            $layer = Layer::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Layer created successfully',
                'data' => $layer
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $layer = Layer::with('features')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $layer
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layer not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $layer = Layer::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:layers,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'geometry_type' => 'nullable|string|in:POINT,LINESTRING,POLYGON,MULTIPOINT,MULTILINESTRING,MULTIPOLYGON,GEOMETRY',
                'is_visible' => 'boolean',
                'style' => 'nullable|array',
            ]);

            $layer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Layer updated successfully',
                'data' => $layer->fresh()
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layer not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $layer = Layer::findOrFail($id);
            
            $featuresCount = $layer->features()->count();
            
            $layer->delete();

            return response()->json([
                'success' => true,
                'message' => "Layer deleted successfully. {$featuresCount} features were also removed."
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layer not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function geojson(int $id): JsonResponse
    {
        try {
            $layer = Layer::findOrFail($id);
            $features = $layer->features()->get();

            $featureCollection = [
                'type' => 'FeatureCollection',
                'properties' => [
                    'layer_id' => $layer->id,
                    'layer_name' => $layer->name,
                    'layer_description' => $layer->description,
                    'geometry_type' => $layer->geometry_type,
                    'is_visible' => $layer->is_visible,
                    'style' => $layer->style ?? null,
                ],
                'features' => $features->map(function ($feature) {
                    return [
                        'type' => 'Feature',
                        'geometry' => $feature->geometry,
                        'properties' => array_merge(
                            $feature->properties ?? [],
                            [
                                'id' => $feature->id,
                                'created_at' => $feature->created_at,
                                'updated_at' => $feature->updated_at,
                            ]
                        )
                    ];
                })->toArray()
            ];

            return response()->json([
                'success' => true,
                'data' => $featureCollection
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layer not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating GeoJSON',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleVisibility(int $id): JsonResponse
    {
        try {
            $layer = Layer::findOrFail($id);
            $layer->update(['is_visible' => !$layer->is_visible]);

            return response()->json([
                'success' => true,
                'message' => 'Layer visibility toggled successfully',
                'data' => [
                    'id' => $layer->id,
                    'is_visible' => $layer->is_visible
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layer not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling layer visibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function duplicate(int $id): JsonResponse
    {
        try {
            $originalLayer = Layer::findOrFail($id);
            
            $newLayer = $originalLayer->replicate();
            $newLayer->name = $originalLayer->name . ' (Copy)';
            $newLayer->save();

            return response()->json([
                'success' => true,
                'message' => 'Layer duplicated successfully',
                'data' => $newLayer
            ], 201);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Layer not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error duplicating layer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}