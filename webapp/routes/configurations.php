<?php


use App\Http\Controllers\Configurations\MapController;
use App\Http\Controllers\Configurations\AisServerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    
    Route::redirect('configurations', 'configurations/map-settings');

    Route::get('configurations/map-settings', [MapController::class, 'edit'])->name('map.edit');
    Route::patch('configurations/map-settings', [MapController::class, 'update'])->name('map.update');

    Route::get('configurations/ais-server', [AisServerController::class, 'edit'])->name('ais-server.edit');
    Route::patch('configurations/ais-server', [AisServerController::class, 'update'])->name('ais-server.update');

});
