<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtWorkController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/exercise-2-tier-pricing', [ArtWorkController::class, 'index']);
Route::post('/exercise-1-artwork-version', [ArtWorkController::class, 'exercise1']);
Route::post('/exercise-3-cart-validator', [ArtWorkController::class, 'exercise3']);
Route::post('/exercise-4-vendor-allocation', [ArtWorkController::class, 'exercise4']);