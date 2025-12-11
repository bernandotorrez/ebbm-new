<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiTestController;
use App\Http\Controllers\FilePreviewController;

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

// File Preview Routes (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/preview/sp3m-lampiran/{id}', [FilePreviewController::class, 'previewSp3mLampiran'])
        ->name('preview.sp3m-lampiran');
    Route::get('/download/sp3m-lampiran/{id}', [FilePreviewController::class, 'downloadSp3mLampiran'])
        ->name('download.sp3m-lampiran');
    Route::get('/export/sp3m-pdf/{id}', [\App\Http\Controllers\Sp3mPdfController::class, 'exportPdf'])
        ->name('export.sp3m-pdf');
    Route::get('/download/sp3m-pdf/{id}', [\App\Http\Controllers\Sp3mPdfController::class, 'downloadPdf'])
        ->name('download.sp3m-pdf');
});
