<?php

use App\Http\Controllers\FarmerController;
use Illuminate\Support\Facades\Route;

/**
 * Farmer Management Routes
 */

Route::prefix('farmers')->group(function () {
    
    // Farmer CRUD routes
    Route::get('/', [FarmerController::class, 'index'])
        ->name('farmers.index');
    
    Route::post('/', [FarmerController::class, 'store'])
        ->name('farmers.store');
    
    Route::get('/{id}', [FarmerController::class, 'show'])
        ->name('farmers.show');
    
    Route::put('/{id}', [FarmerController::class, 'update'])
        ->name('farmers.update');

    // Farmer land holdings routes
    Route::get('/{id}/land-holdings', [FarmerController::class, 'landHoldings'])
        ->name('farmers.land-holdings');
    
    Route::post('/{id}/land-holdings', [FarmerController::class, 'addLandHolding'])
        ->name('farmers.add-land-holding');
    
    Route::put('/land-holdings/{holdingId}/acquisition-status', [FarmerController::class, 'updateAcquisitionStatus'])
        ->name('farmers.update-acquisition-status');

    // Farmer transactions routes
    Route::get('/{id}/transactions', [FarmerController::class, 'transactions'])
        ->name('farmers.transactions');
    
    Route::post('/{id}/transactions', [FarmerController::class, 'addTransaction'])
        ->name('farmers.add-transaction');

    // Farmer loans routes
    Route::get('/{id}/loans', [FarmerController::class, 'loans'])
        ->name('farmers.loans');

    // Farmer support requests routes
    Route::get('/{id}/support-requests', [FarmerController::class, 'supportRequests'])
        ->name('farmers.support-requests');
    
    Route::post('/{id}/support-requests', [FarmerController::class, 'createSupportRequest'])
        ->name('farmers.create-support-request');

    // Farmer dashboard and analytics
    Route::get('/{id}/dashboard', [FarmerController::class, 'dashboard'])
        ->name('farmers.dashboard');
    
    Route::get('/stats', [FarmerController::class, 'stats'])
        ->name('farmers.stats');
    
    Route::get('/summary', [FarmerController::class, 'summary'])
        ->name('farmers.summary');

    // Search route
    Route::get('/search', [FarmerController::class, 'search'])
        ->name('farmers.search');
});
