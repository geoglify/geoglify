<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FeatureController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $features = Feature::with('layer')->get();
            return response()->json([
                'success' => true,
                'data' => $features,
                'total' => $features->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching features',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'layer_id' => 'nullable|integer|exists:layers,id',
                'geometry' => 'required|array',
                'geometry.type' => 'required|string|in:Point,LineString,Polygon,MultiPoint,MultiLineString,MultiPolygon,GeometryCollection',
                'geometry.coordinates' => 'required|array',
                'properties' => 'nullable|array',
            ]);

            $feature = Feature::create([
                'layer_id' => $validated['layer_id'] ?? null,
                'geometry' => $validated['geometry'],
                'properties' => $validated['properties'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feature created successfully',
                'data' => $feature->load('layer')
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
                'message' => 'Error creating feature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $feature = Feature::with('layer')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $feature
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Feature not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching feature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $feature = Feature::findOrFail($id);

            $validated = $request->validate([
                'layer_id' => 'nullable|integer|exists:layers,id',
                'geometry' => 'sometimes|required|array',
                'geometry.type' => 'sometimes|required|string|in:Point,LineString,Polygon,MultiPoint,MultiLineString,MultiPolygon,GeometryCollection',
                'geometry.coordinates' => 'sometimes|required|array',
                'properties' => 'nullable|array',
            ]);

            $feature->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Feature updated successfully',
                'data' => $feature->load('layer')
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Feature not found'
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
                'message' => 'Error updating feature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $feature = Feature::findOrFail($id);
            $feature->delete();

            return response()->json([
                'success' => true,
                'message' => 'Feature deleted successfully'
            ], 204);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Feature not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting feature',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function geojson(int $id): JsonResponse
    {
        try {
            $feature = Feature::findOrFail($id);
            
            $geojson = [
                'type' => 'Feature',
                'geometry' => $feature->geometry,
                'properties' => array_merge(
                    $feature->properties ?? [],
                    [
                        'id' => $feature->id,
                        'layer_id' => $feature->layer_id,
                        'created_at' => $feature->created_at,
                        'updated_at' => $feature->updated_at,
                    ]
                )
            ];

            return response()->json([
                'success' => true,
                'data' => $geojson
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Feature not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating GeoJSON',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function geojsonCollection(Request $request): JsonResponse
    {
        try {
            $query = Feature::query();

            if ($request->has('layer_id')) {
                $query->where('layer_id', $request->layer_id);
            }

            $features = $query->get();

            $featureCollection = [
                'type' => 'FeatureCollection',
                'features' => $features->map(function ($feature) {
                    return [
                        'type' => 'Feature',
                        'geometry' => $feature->geometry,
                        'properties' => array_merge(
                            $feature->properties ?? [],
                            [
                                'id' => $feature->id,
                                'layer_id' => $feature->layer_id,
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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating FeatureCollection',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}