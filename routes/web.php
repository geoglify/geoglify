<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShipController;

Route::get('/', function () {
    // redirect to dashboard index
    return redirect()->route('dashboard.index');
});

Route::resource('dashboard', DashboardController::class)->middleware(['auth', 'verified']);
Route::resource('ships', ShipController::class)->middleware(['auth', 'verified']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
