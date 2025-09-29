<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (if any)
Route::middleware('api')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });
});

// Protected routes (require authentication)
Route::middleware(['auth:api'])->group(function () {
    // Leads API endpoints
    Route::prefix('leads')->group(function () {
        // Get all leads with pagination, filtering, and sorting
        Route::get('/', [LeadController::class, 'index']);
        
        // Create a new lead
        Route::post('/', [LeadController::class, 'store']);
        
        // Get a single lead by ID
        Route::get('/{id}', [LeadController::class, 'show']);
        
        // Update a lead
        Route::put('/{id}', [LeadController::class, 'update']);
        
        // Delete a lead
        Route::delete('/{id}', [LeadController::class, 'destroy']);
        
        // Lead status management
        Route::post('/{id}/status', [LeadController::class, 'updateStatus']);
        
        // Lead assignment
        Route::post('/{id}/assign', [LeadController::class, 'assign']);
        
        // Notes management
        Route::prefix('{lead}/notes')->group(function () {
            Route::get('/', [LeadController::class, 'getNotes']);
            Route::post('/', [LeadController::class, 'addNote']);
            Route::put('/{note}', [LeadController::class, 'updateNote']);
            Route::delete('/{note}', [LeadController::class, 'deleteNote']);
        });
        
        // Files management
        Route::prefix('{lead}/files')->group(function () {
            Route::get('/', [LeadController::class, 'getFiles']);
            Route::post('/', [LeadController::class, 'uploadFile']);
            Route::delete('/{file}', [LeadController::class, 'deleteFile']);
            Route::get('/{file}/download', [LeadController::class, 'downloadFile']);
        });
        
        // Activities
        Route::get('/{lead}/activities', [LeadController::class, 'getActivities']);
        
        // Tags management
        Route::prefix('{lead}/tags')->group(function () {
            Route::get('/', [LeadController::class, 'getTags']);
            Route::post('/', [LeadController::class, 'addTag']);
            Route::delete('/{tag}', [LeadController::class, 'removeTag']);
        });
        
        // Custom fields
        Route::prefix('{lead}/custom-fields')->group(function () {
            Route::get('/', [LeadController::class, 'getCustomFields']);
            Route::post('/', [LeadController::class, 'updateCustomFields']);
        });
        
        // Deals
        Route::prefix('{lead}/deals')->group(function () {
            Route::get('/', [LeadController::class, 'getDeals']);
            Route::post('/', [LeadController::class, 'createDeal']);
            Route::put('/{deal}', [LeadController::class, 'updateDeal']);
            Route::delete('/{deal}', [LeadController::class, 'deleteDeal']);
        });
        
        // Bulk actions
        Route::post('/bulk/delete', [LeadController::class, 'bulkDelete']);
        Route::post('/bulk/status', [LeadController::class, 'bulkUpdateStatus']);
        Route::post('/bulk/assign', [LeadController::class, 'bulkAssign']);
        
        // Import/Export
        Route::post('/import', [LeadController::class, 'import']);
        Route::get('/export', [LeadController::class, 'export']);
        
        // Statistics
        Route::get('/stats/overview', [LeadController::class, 'getOverviewStats']);
        Route::get('/stats/status', [LeadController::class, 'getStatusStats']);
        Route::get('/stats/source', [LeadController::class, 'getSourceStats']);
        Route::get('/stats/assigned-to', [LeadController::class, 'getAssignedToStats']);
        Route::get('/stats/created-by', [LeadController::class, 'getCreatedByStats']);
        Route::get('/stats/timeline', [LeadController::class, 'getTimelineStats']);
    });
    
    // Lookup endpoints (for dropdowns, etc.)
    Route::prefix('lookup')->group(function () {
        Route::get('statuses', [LeadController::class, 'getStatuses']);
        Route::get('sources', [LeadController::class, 'getSources']);
        Route::get('tags', [LeadController::class, 'getAllTags']);
        Route::get('users', [LeadController::class, 'getUsers']);
        Route::get('custom-fields', [LeadController::class, 'getCustomFieldDefinitions']);
        Route::get('deal-stages', [LeadController::class, 'getDealStages']);
    });
});
