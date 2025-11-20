<?php

/**
 * API Routes Configuration
 * Custom Framework API Routes (Converted from Laravel)
 */

// API Route Definitions
$apiRoutes = [
    // Public routes (no authentication required)
    'public' => [
        'GET' => [
            '/api/health' => function() {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'ok', 'message' => 'API is running']);
                exit;
            },

        ],
    ],

    // Protected routes (require authentication)
    'protected' => [
        'GET' => [
            '/api/leads' => 'ApiLeadController@index',
            '/api/leads/{id}' => 'ApiLeadController@show',
            '/api/leads/{lead}/notes' => 'ApiLeadController@getNotes',
            '/api/leads/{lead}/files' => 'ApiLeadController@getFiles',
            '/api/leads/{lead}/activities' => 'ApiLeadController@getActivities',
            '/api/leads/{lead}/tags' => 'ApiLeadController@getTags',
            '/api/leads/{lead}/custom-fields' => 'ApiLeadController@getCustomFields',
            '/api/leads/{lead}/deals' => 'ApiLeadController@getDeals',
            '/api/leads/stats/overview' => 'ApiLeadController@getStats',
            '/api/leads/stats/status' => 'ApiLeadController@getStatusStats',
            '/api/leads/stats/source' => 'ApiLeadController@getSourceStats',
            '/api/leads/stats/assigned-to' => 'ApiLeadController@getAssignedToStats',
            '/api/leads/stats/created-by' => 'ApiLeadController@getCreatedByStats',
            '/api/leads/stats/timeline' => 'ApiLeadController@getTimelineStats',
            '/api/lookup/statuses' => 'ApiLeadController@getStatuses',
            '/api/lookup/sources' => 'ApiLeadController@getSources',
            '/api/lookup/tags' => 'ApiLeadController@getAllTags',
            '/api/lookup/users' => 'ApiLeadController@getUsers',
            '/api/lookup/custom-fields' => 'ApiLeadController@getCustomFieldDefinitions',
            '/api/lookup/deal-stages' => 'ApiLeadController@getDealStages',
            '/api/leads/{file}/download' => 'ApiLeadController@downloadFile',
        ],

        'POST' => [
            '/api/leads' => 'ApiLeadController@store',
            '/api/leads/{lead}/notes' => 'ApiLeadController@addNote',
            '/api/leads/{lead}/files' => 'ApiLeadController@uploadFile',
            '/api/leads/{lead}/status' => 'ApiLeadController@updateStatus',
            '/api/leads/{lead}/assign' => 'ApiLeadController@assign',
            '/api/leads/{lead}/tags' => 'ApiLeadController@addTag',
            '/api/leads/{lead}/custom-fields' => 'ApiLeadController@updateCustomFields',
            '/api/leads/{lead}/deals' => 'ApiLeadController@createDeal',
            '/api/leads/bulk/delete' => 'ApiLeadController@bulkDelete',
            '/api/leads/bulk/status' => 'ApiLeadController@bulkUpdateStatus',
            '/api/leads/bulk/assign' => 'ApiLeadController@bulkAssign',
            '/api/leads/import' => 'ApiLeadController@import',
        ],

        'PUT' => [
            '/api/leads/{id}' => 'ApiLeadController@update',
            '/api/leads/{lead}/notes/{note}' => 'ApiLeadController@updateNote',
            '/api/leads/{lead}/deals/{deal}' => 'ApiLeadController@updateDeal',
        ],

        'DELETE' => [
            '/api/leads/{id}' => 'ApiLeadController@destroy',
            '/api/leads/{lead}/notes/{note}' => 'ApiLeadController@deleteNote',
            '/api/leads/{lead}/files/{file}' => 'ApiLeadController@deleteFile',
            '/api/leads/{lead}/tags/{tag}' => 'ApiLeadController@removeTag',
            '/api/leads/{lead}/deals/{deal}' => 'ApiLeadController@deleteDeal',
        ],
    ],
];

// Export routes for use in router
return $apiRoutes;
