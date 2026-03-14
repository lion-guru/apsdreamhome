<?php

/**
 * APS Dream Home V2 Smart Sync Routes
 * These routes handle the mobile app sync functionality
 */

use App\Http\Controllers\Api\MobileApiController;

// API Routes for Mobile App (V2 with Smart Sync)
Route::group(['prefix' => 'api/v2/mobile', 'middleware' => ['cors']], function () {

    // Authentication Routes
    Route::post('/auth/login', [MobileApiController::class, 'login']);
    Route::post('/auth/refresh', [MobileApiController::class, 'refreshToken']);
    Route::post('/auth/logout', [MobileApiController::class, 'logout']);

    // Sync Routes (V2 Smart Sync)
    Route::post('/sync', [MobileApiController::class, 'sync']);
    Route::get('/sync/status/{user_id}', [MobileApiController::class, 'syncStatus']);
    Route::post('/sync/upload-batch', [MobileApiController::class, 'batchSync']);

    // Property Routes (Merged - Legacy + V2 Sync)
    Route::get('/properties', [MobileApiController::class, 'properties']);
    Route::get('/properties/{id}', [MobileApiController::class, 'property']);
    Route::post('/properties/{id}/inquiry', [MobileApiController::class, 'propertyInquiry']);

    // Lead Routes (V2 with Offline Support)
    Route::get('/leads', [MobileApiController::class, 'leads']);
    Route::post('/leads', [MobileApiController::class, 'submitLead']);
    Route::post('/leads/batch', [MobileApiController::class, 'batchSyncLeads']);
    Route::put('/leads/{id}/status', [MobileApiController::class, 'updateLeadStatus']);

    // Commission Routes (V2)
    Route::get('/commissions', [MobileApiController::class, 'commissions']);
    Route::get('/commissions/summary', [MobileApiController::class, 'commissionSummary']);

    // User Routes (V2)
    Route::get('/user/profile', [MobileApiController::class, 'userProfile']);
    Route::put('/user/profile', [MobileApiController::class, 'updateProfile']);
    Route::get('/user/team', [MobileApiController::class, 'userTeam']);

    // Admin Routes (V2)
    Route::post('/admin/properties/bulk-update', [MobileApiController::class, 'bulkUpdateProperties']);
    Route::get('/admin/sync/queue', [MobileApiController::class, 'syncQueue']);
    Route::delete('/admin/sync/clear', [MobileApiController::class, 'clearSyncQueue']);

    // Monitoring Routes
    Route::get('/monitoring/health', [MobileApiController::class, 'healthCheck']);
    Route::get('/monitoring/stats', [MobileApiController::class, 'systemStats']);
});

// Legacy API Routes (Backward Compatibility)
Route::group(['prefix' => 'api/v1/mobile', 'middleware' => ['cors']], function () {

    // Legacy Property Routes
    Route::get('/properties', [MobileApiController::class, 'properties']);
    Route::get('/properties/{id}', [MobileApiController::class, 'property']);

    // Legacy Lead Routes
    Route::get('/leads', [MobileApiController::class, 'leads']);
    Route::post('/leads', [MobileApiController::class, 'submitLead']);

    // Legacy User Routes
    Route::get('/user/profile', [MobileApiController::class, 'userProfile']);
});

// Web Routes for Admin Dashboard (if needed)
Route::group(['prefix' => 'admin/mobile', 'middleware' => ['auth', 'admin']], function () {

    // Sync Management
    Route::get('/sync/dashboard', [MobileApiController::class, 'syncDashboard']);
    Route::post('/sync/force/{user_id}', [MobileApiController::class, 'forceSync']);

    // Mobile User Management
    Route::get('/users', [MobileApiController::class, 'mobileUsers']);
    Route::delete('/users/{id}', [MobileApiController::class, 'revokeDevice']);

    // Analytics
    Route::get('/analytics/usage', [MobileApiController::class, 'usageAnalytics']);
    Route::get('/analytics/sync', [MobileApiController::class, 'syncAnalytics']);
});
