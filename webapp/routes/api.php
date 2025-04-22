<?php

use App\Http\Controllers\Api\AisController;
use Illuminate\Support\Facades\Route;

Route::post('/ais', [AisController::class, 'store'])->name('ais.store');
