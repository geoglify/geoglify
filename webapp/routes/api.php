<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LayerController;
use App\Http\Controllers\Api\FeatureController;

Route::prefix('api')->group(function () {
    Route::get('/layers', [LayerController::class, 'index'])->name('api.layers.index');
    Route::post('/layers', [LayerController::class, 'store'])->name('api.layers.store');
    Route::get('/layers/{id}', [LayerController::class, 'show'])->name('api.layers.show');
    Route::put('/layers/{id}', [LayerController::class, 'update'])->name('api.layers.update');
    Route::delete('/layers/{id}', [LayerController::class, 'destroy'])->name('api.layers.destroy');
    Route::get('layers/{layer}/geojson', [LayerController::class, 'geojson']);

    Route::get('/features', [FeatureController::class, 'index'])->name('api.features.index');
    Route::post('/features', [FeatureController::class, 'store'])->name('api.features.store');
    Route::get('/features/{id}', [FeatureController::class, 'show'])->name('api.features.show');
    Route::put('/features/{id}', [FeatureController::class, 'update'])->name('api.features.update');
    Route::delete('/features/{id}', [FeatureController::class, 'destroy'])->name('api.features.destroy'); 
    Route::get('features/{feature}/geojson', [FeatureController::class, 'geojson']);
});
