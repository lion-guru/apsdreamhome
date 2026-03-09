<?php

use App\Http\Controllers\CoreFunctionsController;
use Illuminate\Support\Facades\Route;

/**
 * Core Functions Routes
 */

Route::prefix('core-functions')->group(function () {
    
    // Input validation routes
    Route::post('/validate', [CoreFunctionsController::class, 'validateInput'])
        ->name('core-functions.validate');
    
    Route::post('/validate-multiple', [CoreFunctionsController::class, 'validateInputs'])
        ->name('core-functions.validate-multiple');

    // Text processing routes
    Route::post('/format-phone', [CoreFunctionsController::class, 'formatPhone'])
        ->name('core-functions.format-phone');
    
    Route::post('/generate-slug', [CoreFunctionsController::class, 'generateSlug'])
        ->name('core-functions.generate-slug');
    
    Route::post('/truncate-text', [CoreFunctionsController::class, 'truncateText'])
        ->name('core-functions.truncate-text');
    
    Route::post('/generate-random', [CoreFunctionsController::class, 'generateRandomString'])
        ->name('core-functions.generate-random');

    // Formatting routes
    Route::post('/format-currency', [CoreFunctionsController::class, 'formatCurrency'])
        ->name('core-functions.format-currency');
    
    Route::post('/format-date', [CoreFunctionsController::class, 'formatDate'])
        ->name('core-functions.format-date');

    // File handling routes
    Route::post('/upload-image', [CoreFunctionsController::class, 'uploadImage'])
        ->name('core-functions.upload-image');
    
    Route::get('/file-info', [CoreFunctionsController::class, 'getFileInfo'])
        ->name('core-functions.file-info');
    
    Route::post('/extract-text', [CoreFunctionsController::class, 'extractText'])
        ->name('core-functions.extract-text');

    // Client information routes
    Route::get('/client-info', [CoreFunctionsController::class, 'getClientInfo'])
        ->name('core-functions.client-info');
    
    Route::get('/csrf-token', [CoreFunctionsController::class, 'getCsrfToken'])
        ->name('core-functions.csrf-token');

    // Logging routes
    Route::post('/log-action', [CoreFunctionsController::class, 'logAction'])
        ->name('core-functions.log-action');

    // Testing route
    Route::get('/test', [CoreFunctionsController::class, 'test'])
        ->name('core-functions.test');
});
