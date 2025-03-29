<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShipController;

// Redirect to the dashboard index
Route::get('/', fn() => redirect()->route('dashboard.index'));

// Dashboard routes (protected with authentication and verification middleware)
Route::resource('dashboard', DashboardController::class)->middleware(['auth', 'verified']);

// Ship routes (protected with authentication and verification middleware)
Route::resource('ships', ShipController::class)->middleware(['auth', 'verified']);

// Ship last position route
Route::get('ships/{ship}/last-positions/{seconds}', [ShipController::class, 'lastPositions'])->middleware(['auth', 'verified']);

// Grouping real-time ship-related routes under a common prefix
Route::prefix('realtime/ships')->middleware(['auth', 'verified'])->group(function () {
    Route::get('count', [ShipController::class, 'countShips']); // Get total number of ships
    Route::get('top-categories', [ShipController::class, 'topCategories']); // Get top ship categories
    Route::get('top-countries', [ShipController::class, 'topCountries']); // Get ship count per country
});

// Include additional route files
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
