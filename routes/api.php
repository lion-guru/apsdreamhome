<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VersionController;
use App\Http\Controllers\Api\V1\PropertyController as PropertyControllerV1;
use App\Http\Controllers\Api\V1\UserController as UserControllerV1;
use App\Http\Controllers\Api\V2\PropertyController as PropertyControllerV2;
use App\Http\Controllers\Api\V2\UserController as UserControllerV2;
use App\Http\Controllers\Api\V2\MLController;
use App\Http\Controllers\Api\V2\AnalyticsController;
use App\Http\Controllers\Api\V2\RealtimeController;

// API versioning middleware
Route::middleware(['api.versioning'])->group(function () {
    
    // Version information endpoints
    Route::get('/version', [VersionController::class, 'index']);
    Route::get('/version/documentation', [VersionController::class, 'documentation']);
    
    // v1.0 routes (deprecated)
    Route::prefix('v1.0')->group(function () {
        Route::apiResource('properties', PropertyControllerV1::class);
        Route::apiResource('users', UserControllerV1::class);
        
        // Additional v1.0 specific routes
        Route::get('properties/search', [PropertyControllerV1::class, 'search']);
        Route::get('users/{id}/favorites', [UserControllerV1::class, 'favorites']);
    });
    
    // v1.1 routes (stable)
    Route::prefix('v1.1')->group(function () {
        Route::apiResource('properties', PropertyControllerV1::class);
        Route::apiResource('users', UserControllerV1::class);
        
        // Enhanced v1.1 specific routes
        Route::get('properties/search', [PropertyControllerV1::class, 'enhancedSearch']);
        Route::get('users/{id}/favorites', [UserControllerV1::class, 'favorites']);
        Route::get('users/{id}/analytics', [UserControllerV1::class, 'analytics']);
        
        // Webhook routes
        Route::prefix('webhooks')->group(function () {
            Route::get('/', [WebhookController::class, 'index']);
            Route::post('/', [WebhookController::class, 'store']);
            Route::delete('/{id}', [WebhookController::class, 'destroy']);
        });
    });
    
    // v2.0 routes (latest)
    Route::prefix('v2.0')->group(function () {
        // Properties with ML features
        Route::apiResource('properties', PropertyControllerV2::class);
        Route::get('properties/search', [PropertyControllerV2::class, 'aiSearch']);
        Route::get('properties/{id}/recommendations', [PropertyControllerV2::class, 'recommendations']);
        Route::get('properties/{id}/analytics', [PropertyControllerV2::class, 'analytics']);
        
        // Users with advanced features
        Route::apiResource('users', UserControllerV2::class);
        Route::get('users/{id}/analytics', [UserControllerV2::class, 'analytics']);
        Route::get('users/{id}/behavior', [UserControllerV2::class, 'behavior']);
        Route::get('users/{id}/predictions', [UserControllerV2::class, 'predictions']);
        
        // Machine Learning endpoints
        Route::prefix('ml')->group(function () {
            Route::get('recommendations/{user_id}', [MLController::class, 'recommendations']);
            Route::get('predict-price/{property_id}', [MLController::class, 'predictPrice']);
            Route::get('analyze-user/{user_id}', [MLController::class, 'analyzeUser']);
            Route::get('detect-fraud/{user_id}', [MLController::class, 'detectFraud']);
            Route::get('market-trends', [MLController::class, 'marketTrends']);
            Route::get('price-prediction', [MLController::class, 'pricePrediction']);
        });
        
        // Real-time endpoints
        Route::prefix('realtime')->group(function () {
            Route::get('updates', [RealtimeController::class, 'updates']);
            Route::post('subscribe', [RealtimeController::class, 'subscribe']);
            Route::delete('unsubscribe', [RealtimeController::class, 'unsubscribe']);
            Route::get('notifications', [RealtimeController::class, 'notifications']);
        });
        
        // Analytics endpoints
        Route::prefix('analytics')->group(function () {
            Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
            Route::get('reports', [AnalyticsController::class, 'reports']);
            Route::get('metrics', [AnalyticsController::class, 'metrics']);
            Route::get('overview', [AnalyticsController::class, 'overview']);
            Route::get('performance', [AnalyticsController::class, 'performance']);
        });
        
        // Enhanced search endpoints
        Route::prefix('search')->group(function () {
            Route::get('properties', [SearchController::class, 'properties']);
            Route::get('users', [SearchController::class, 'users']);
            Route::get('suggestions', [SearchController::class, 'suggestions']);
            Route::get('trending', [SearchController::class, 'trending']);
        });
        
        // Notification endpoints
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/', [NotificationController::class, 'store']);
            Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
        });
        
        // File upload endpoints
        Route::prefix('files')->group(function () {
            Route::post('upload', [FileController::class, 'upload']);
            Route::get('{id}', [FileController::class, 'show']);
            Route::delete('{id}', [FileController::class, 'destroy']);
        });
    });
    
    // Default to latest version
    Route::fallback(function () {
        return Route::prefix('v2.0')->group(function () {
            // Include all v2.0 routes as fallback
            Route::apiResource('properties', PropertyControllerV2::class);
            Route::apiResource('users', UserControllerV2::class);
            // ... other v2.0 routes
        });
    });
});

// Health check endpoint (no versioning)
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version'),
        'environment' => config('app.env')
    ]);
});

// API documentation endpoint
Route::get('/docs', function () {
    return response()->json([
        'title' => 'APS Dream Home API',
        'description' => 'Real estate property management API',
        'version' => '2.0',
        'base_url' => config('app.url') . '/api',
        'documentation' => config('app.url') . '/api/v2.0/documentation',
        'support' => 'support@apsdreamhome.com'
    ]);
});
