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
Route::post('/exercise-5-discount', [ArtWorkController::class, 'exercise5']);
Route::post('/exercise-6-approval-flow', [ArtWorkController::class, 'exercise6']);
Route::post('/exercise-7-inventory', [ArtWorkController::class, 'exercise7']);
Route::post('/exercise-8-shipment', [ArtWorkController::class, 'exercise8']);
Route::post('/exercise-9-webhook', [ArtWorkController::class, 'exercise9']);
Route::post('/exercise-10-quote-expiry', [ArtWorkController::class, 'exercise10']);
Route::post('/exercise-11-product-visibility', [ArtWorkController::class, 'exercise11']);
Route::post('/exercise-12-bundle-pricing', [ArtWorkController::class, 'exercise12']);
Route::post('/exercise-13-cart-merge', [ArtWorkController::class, 'exercise13']);
Route::post('/exercise-14-upsell', [ArtWorkController::class, 'exercise14']);