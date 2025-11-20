<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiTestController;

Route::get('/', function () {
    return view('welcome');
});

// API Testing Routes
Route::prefix('api-test')->group(function () {
    // Test SIMPEG API - JSON Response
    Route::get('/simpeg', [ApiTestController::class, 'simpegTest']);
    
    // Test SIMPEG API with custom NIP - JSON Response
    Route::get('/simpeg/{nip}', [ApiTestController::class, 'simpegTestByNip']);
    
    // Test SIMPEG API - Browser View
    Route::get('/simpeg-view', [ApiTestController::class, 'simpegTestView']);
});
