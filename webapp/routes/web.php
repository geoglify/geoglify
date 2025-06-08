<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

//! To avoid the 503 error, ensure that the application is properly configured and running.
//Route::get( '/', fn() => App::abort( 503 ) );

Route::get('/', function () {
    return redirect()->route('map.index');
})->name('home');

Route::post('/set-locale', function () {
    $locale = request('locale');
    if (in_array($locale, ['pt', 'en', 'es', 'fr'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
})->name('locale.set');

Route::middleware(['auth', 'verified'])->group(function () {

    // Map
    Route::get('/map', [MapController::class, 'index'])->name('map.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
