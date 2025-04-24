<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RealtimeController;
use App\Http\Controllers\TrafficController;
use App\Http\Controllers\ShipController;

// Redirect to the realtime index
Route::get('/', fn() => redirect()->route('realtime.index'));

// Realtime routes (protected with authentication and verification middleware)
Route::resource('realtime', RealtimeController::class)->middleware(['auth', 'verified']);

// Traffic analysis routes (protected with authentication and verification middleware)
Route::resource('traffic', TrafficController::class)->middleware(['auth', 'verified']);

// Ship routes (protected with authentication and verification middleware)
Route::resource('ships', ShipController::class)->middleware(['auth', 'verified']);

// Ship last position route
Route::post('ships/{ship}/last-positions', [ShipController::class, 'lastPositions'])->middleware(['auth', 'verified']);

// Include additional route files
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
