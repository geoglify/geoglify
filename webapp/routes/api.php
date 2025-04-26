<?php

use App\Http\Controllers\Api\AisController;
use Illuminate\Support\Facades\Route;

Route::post('/ais', [AisController::class, 'store'])->name('ais.store');

Route::get('/ais-heatmap', [AisController::class, 'heatmap'])->name('ais.heatmap');

Route::get('/ais-configuration', [AisController::class, 'config'])->name('ais.config');
