<?php

use App\Core\App;

/** @var App $app */

// API Route Definitions

$app->router()->group(['prefix' => 'api'], function ($router) {

    // Health check
    $router->get('/health', function () {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'message' => 'API is running']);
        exit;
    });

    // Search Routes (Legacy compatibility for search.js)
    $router->get('/properties', 'Api\PropertyController@index'); // Modern endpoint for search.js
    $router->post('/search.php', 'Api\PropertyController@search');
    $router->get('/get_saved_searches.php', 'Api\PropertyController@getSavedSearches');
    $router->get('/get_saved_search.php', 'Api\PropertyController@getSavedSearch');
    $router->post('/save_search.php', 'Api\PropertyController@saveSearch');
    $router->post('/delete_search.php', 'Api\PropertyController@deleteSearch');

    // Leads Routes
    $router->group(['middleware' => 'auth'], function ($router) {
        $router->get('/leads', 'Api\ApiLeadController@index');
        $router->post('/leads', 'Api\ApiLeadController@store');
        $router->get('/leads/{id}', 'Api\ApiLeadController@show');
        $router->put('/leads/{id}', 'Api\ApiLeadController@update');
        $router->delete('/leads/{id}', 'Api\ApiLeadController@destroy');

        $router->get('/leads/{lead}/notes', 'Api\ApiLeadController@getNotes');
        $router->post('/leads/{lead}/notes', 'Api\ApiLeadController@addNote');
        $router->put('/leads/{lead}/notes/{note}', 'Api\ApiLeadController@updateNote');
        $router->delete('/leads/{lead}/notes/{note}', 'Api\ApiLeadController@deleteNote');

        $router->get('/leads/{lead}/files', 'Api\ApiLeadController@getFiles');
        $router->post('/leads/{lead}/files', 'Api\ApiLeadController@uploadFile');
        $router->delete('/leads/{lead}/files/{file}', 'Api\ApiLeadController@deleteFile');

        $router->get('/leads/{lead}/activities', 'Api\ApiLeadController@getActivities');

        $router->get('/leads/{lead}/tags', 'Api\ApiLeadController@getTags');
        $router->post('/leads/{lead}/tags', 'Api\ApiLeadController@addTag');
        $router->delete('/leads/{lead}/tags/{tag}', 'Api\ApiLeadController@removeTag');

        $router->get('/leads/{lead}/custom-fields', 'Api\ApiLeadController@getCustomFields');
        $router->post('/leads/{lead}/custom-fields', 'Api\ApiLeadController@updateCustomFields');

        $router->get('/leads/{lead}/deals', 'Api\ApiLeadController@getDeals');
        $router->post('/leads/{lead}/deals', 'Api\ApiLeadController@createDeal');
        $router->put('/leads/{lead}/deals/{deal}', 'Api\ApiLeadController@updateDeal');
        $router->delete('/leads/{lead}/deals/{deal}', 'Api\ApiLeadController@deleteDeal');

        $router->put('/leads/{lead}/status', 'Api\ApiLeadController@updateStatus');
        $router->put('/leads/{lead}/assign', 'Api\ApiLeadController@assign');

        // Bulk operations
        $router->post('/leads/bulk/delete', 'Api\ApiLeadController@bulkDelete');
        $router->post('/leads/bulk/status', 'Api\ApiLeadController@bulkUpdateStatus');
        $router->post('/leads/bulk/assign', 'Api\ApiLeadController@bulkAssign');
        $router->post('/leads/import', 'Api\ApiLeadController@import');

        // Stats
        $router->get('/leads/stats/overview', 'Api\ApiLeadController@getStats');
        $router->get('/leads/stats/status', 'Api\ApiLeadController@getStatusStats');
        $router->get('/leads/stats/source', 'Api\ApiLeadController@getSourceStats');
        $router->get('/leads/stats/assigned-to', 'Api\ApiLeadController@getAssignedToStats');
        $router->get('/leads/stats/created-by', 'Api\ApiLeadController@getCreatedByStats');
        $router->get('/leads/stats/timeline', 'Api\ApiLeadController@getTimelineStats');

        // Lookups
        $router->get('/lookup/statuses', 'Api\ApiLeadController@getStatuses');
        $router->get('/lookup/sources', 'Api\ApiLeadController@getSources');
        $router->get('/lookup/tags', 'Api\ApiLeadController@getAllTags');
        $router->get('/lookup/users', 'Api\ApiLeadController@getUsers');
        $router->get('/lookup/custom-fields', 'Api\ApiLeadController@getCustomFieldDefinitions');
        $router->get('/lookup/deal-stages', 'Api\ApiLeadController@getDealStages');

        $router->get('/leads/{file}/download', 'Api\ApiLeadController@downloadFile');
    });
});
