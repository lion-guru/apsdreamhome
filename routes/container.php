<?php

use App\Http\Controllers\ContainerController;
use Illuminate\Support\Facades\Route;

/**
 * Dependency Injection Container Routes
 */

// Container management routes
Route::prefix('container')->group(function () {
    
    // Get all registered services and aliases
    Route::get('/', [ContainerController::class, 'index'])
        ->name('container.index');

    // Register a new service
    Route::post('/register', [ContainerController::class, 'register'])
        ->name('container.register');

    // Check if a service exists
    Route::get('/{id}', [ContainerController::class, 'show'])
        ->name('container.show');

    // Resolve and test a service
    Route::get('/{id}/resolve', [ContainerController::class, 'resolve'])
        ->name('container.resolve');

    // Remove a service
    Route::delete('/{id}', [ContainerController::class, 'destroy'])
        ->name('container.destroy');

    // Clear all services
    Route::delete('/', [ContainerController::class, 'clear'])
        ->name('container.clear');

    // Test container functionality
    Route::get('/test/functionality', [ContainerController::class, 'test'])
        ->name('container.test');

    // Get container statistics
    Route::get('/stats/info', [ContainerController::class, 'stats'])
        ->name('container.stats');
});
