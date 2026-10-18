<?php
/**
 * Gap-build lane routes (S325-S333).
 *
 * Single-owner file: all routes registered here belong to the gap-build
 * feature set (Excel export, GRN, recurring journal entries, scheduled reports,
 * calendar sync, visual workflow builder). Loaded after routes/web.php via the
 * guarded loader in public/index.php (S308 pattern) so they stay separable from
 * the multi-lane churn in routes/web.php.
 *
 * Do NOT add routes here that belong to another lane — keep authorship clean.
 */

// Associate visual workflow builder
$router->get('/associate/workflow-builder', 'App\\Http\\Controllers\\Associate\\CrmController@workflowBuilder');

// Admin Recurring Journal Entries (Gap 27)
$router->get('/admin/recurring-journal-entries', 'App\\Http\\Controllers\\Admin\\RecurringJournalEntryController@index');
$router->get('/admin/recurring-journal-entries/create', 'App\\Http\\Controllers\\Admin\\RecurringJournalEntryController@create');
$router->post('/admin/recurring-journal-entries/store', 'App\\Http\\Controllers\\Admin\\RecurringJournalEntryController@store');
$router->get('/admin/recurring-journal-entries/{id}', 'App\\Http\\Controllers\\Admin\\RecurringJournalEntryController@show');
$router->post('/admin/recurring-journal-entries/execute/{id}', 'App\\Http\\Controllers\\Admin\\RecurringJournalEntryController@execute');
$router->post('/admin/recurring-journal-entries/process-due', 'App\\Http\\Controllers\\Admin\\RecurringJournalEntryController@processDue');
$router->post('/admin/recurring-journal-entries/delete/{id}', 'App\\Http\\Controllers\\Admin\\RecurringJournalEntryController@delete');

// Admin Scheduled Reports (Gap 13)
$router->get('/admin/scheduled-reports', 'App\\Http\\Controllers\\Admin\\ScheduledReportController@index');
$router->get('/admin/scheduled-reports/create', 'App\\Http\\Controllers\\Admin\\ScheduledReportController@create');
$router->post('/admin/scheduled-reports/store', 'App\\Http\\Controllers\\Admin\\ScheduledReportController@store');
$router->get('/admin/scheduled-reports/{id}', 'App\\Http\\Controllers\\Admin\\ScheduledReportController@show');
$router->post('/admin/scheduled-reports/{id}/update', 'App\\Http\\Controllers\\Admin\\ScheduledReportController@update');
$router->post('/admin/scheduled-reports/{id}/run', 'App\\Http\\Controllers\\Admin\\ScheduledReportController@runNow');
$router->post('/admin/scheduled-reports/delete/{id}', 'App\\Http\\Controllers\\Admin\\ScheduledReportController@delete');

// Admin Calendar Sync (Gap 23)
$router->get('/admin/calendar-sync', 'App\\Http\\Controllers\\Admin\\CalendarSyncController@index');
$router->get('/admin/calendar-sync/connect', 'App\\Http\\Controllers\\Admin\\CalendarSyncController@connect');
$router->get('/admin/calendar-sync/callback', 'App\\Http\\Controllers\\Admin\\CalendarSyncController@callback');
$router->post('/admin/calendar-sync/disconnect', 'App\\Http\\Controllers\\Admin\\CalendarSyncController@disconnect');
$router->post('/admin/calendar-sync/sync', 'App\\Http\\Controllers\\Admin\\CalendarSyncController@sync');
$router->get('/admin/calendar-sync/list-events', 'App\\Http\\Controllers\\Admin\\CalendarSyncController@listEvents');

// Admin Excel Export (.xlsx) — Gap 12
$router->get('/admin/export/excel/{report}', 'App\\Http\\Controllers\\Admin\\ExcelExportController@export');
$router->post('/admin/export/excel', 'App\\Http\\Controllers\\Admin\\ExcelExportController@exportGeneric');

// Admin Goods Receipt Note (GRN) — Gap 6
$router->get('/admin/grn', 'App\\Http\\Controllers\\Admin\\GRNController@index');
$router->get('/admin/grn/create', 'App\\Http\\Controllers\\Admin\\GRNController@create');
$router->post('/admin/grn/store', 'App\\Http\\Controllers\\Admin\\GRNController@store');
$router->get('/admin/grn/{id}', 'App\\Http\\Controllers\\Admin\\GRNController@show');
$router->post('/admin/grn/{id}/status', 'App\\Http\\Controllers\\Admin\\GRNController@updateStatus');
$router->post('/admin/grn/delete/{id}', 'App\\Http\\Controllers\\Admin\\GRNController@delete');
