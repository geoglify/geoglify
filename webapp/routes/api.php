<?php

use App\Http\Controllers\AisController;
use Illuminate\Support\Facades\Route;

Route::apiResource('ais', AisController::class);
