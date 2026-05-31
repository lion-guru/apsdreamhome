// Customer Lead Extras Management Routes
$router->get('/admin/customer-lead/behavior', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@behavior');
$router->get('/admin/customer-lead/behavior/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showBehavior');
$router->get('/admin/customer-lead/journeys', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@journeys');
$router->get('/admin/customer-lead/journeys/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showJourney');
$router->get('/admin/customer-lead/lead-scores', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@leadScores');
$router->post('/admin/customer-lead/lead-scores/update/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@updateLeadScore');
$router->get('/admin/customer-lead/events', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@events');
$router->get('/admin/customer-lead/events/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showEvent');
$router->get('/admin/customer-lead/custom-fields', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@customFields');
$router->post('/admin/customer-lead/custom-fields/store', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@storeCustomField');
$router->post('/admin/customer-lead/custom-fields/update/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@updateCustomField');
$router->post('/admin/customer-lead/custom-fields/delete/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@deleteCustomField');
$router->get('/admin/customer-lead/approvals', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@approvals');
$router->get('/admin/customer-lead/approvals/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@showApproval');
$router->post('/admin/customer-lead/approvals/update-status/{id}', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@updateApprovalStatus');
$router->get('/admin/customer-lead/file-extractions', 'App\\Http\\Controllers\\Admin\\CustomerLeadExtrasController@fileExtractions');

// ═══════════════════════════════════════════════════
// HR MANAGEMENT SYSTEM
// ═══════════════════════════════════════════════════

// Dashboard
$router->get('/admin/hr', 'App\\Http\\Controllers\\Admin\\HRController@index');

// users
$router->get('/admin/hr/users', 'App\\Http\\Controllers\\Admin\\HRController@users');
$router->get('/admin/hr/users/create', 'App\\Http\\Controllers\\Admin\\HRController@createEmployee');
$router->post('/admin/hr/users/store', 'App\\Http\\Controllers\\Admin\\HRController@storeEmployee');
$router->get('/admin/hr/users/edit/{id}', 'App\\Http\\Controllers\\Admin\\HRController@editEmployee');
$router->post('/admin/hr/users/update/{id}', 'App\\Http\\Controllers\\Admin\\HRController@updateEmployee');
$router->get('/admin/hr/users/delete/{id}', 'App\\Http\\Controllers\\Admin\\HRController@deleteEmployee');
$router->get('/admin/hr/users/view/{id}', 'App\\Http\\Controllers\\Admin\\HRController@viewEmployee');

// Attendance
$router->get('/admin/hr/attendance', 'App\\Http\\Controllers\\Admin\\HRController@attendance');
$router->post('/admin/hr/attendance/mark', 'App\\Http\\Controllers\\Admin\\HRController@markAttendance');
$router->get('/admin/hr/attendance/report', 'App\\Http\\Controllers\\Admin\\HRController@attendanceReport');

// Leaves
$router->get('/admin/hr/leaves', 'App\\Http\\Controllers\\Admin\\HRController@leaves');
$router->post('/admin/hr/leaves/store', 'App\\Http\\Controllers\\Admin\\HRController@storeLeave');
$router->get('/admin/hr/leaves/approve/{id}', 'App\\Http\\Controllers\\Admin\\HRController@approveLeave');
$router->get('/admin/hr/leaves/reject/{id}', 'App\\Http\\Controllers\\Admin\\HRController@rejectLeave');
$router->get('/admin/hr/leave-types', 'App\\Http\\Controllers\\Admin\\HRController@leaveTypes');
$router->post('/admin/hr/leave-types/store', 'App\\Http\\Controllers\\Admin\\HRController@storeLeaveType');
$router->get('/admin/hr/leave-balances', 'App\\Http\\Controllers\\Admin\\HRController@leaveBalances');

// Shifts
$router->get('/admin/hr/shifts', 'App\\Http\\Controllers\\Admin\\HRController@shifts');
$router->post('/admin/hr/shifts/store', 'App\\Http\\Controllers\\Admin\\HRController@storeShift');
$router->any('/admin/hr/shifts/assign', 'App\\Http\\Controllers\\Admin\\HRController@assignShift');
$router->get('/admin/hr/shifts/schedule', 'App\\Http\\Controllers\\Admin\\HRController@shiftSchedule');

// KPIs
$router->get('/admin/hr/kpis', 'App\\Http\\Controllers\\Admin\\HRController@kpis');
$router->post('/admin/hr/kpis/store', 'App\\Http\\Controllers\\Admin\\HRController@storeKpi');

// Performance
$router->get('/admin/hr/performance', 'App\\Http\\Controllers\\Admin\\HRController@performance');
$router->post('/admin/hr/performance/store', 'App\\Http\\Controllers\\Admin\\HRController@storeReview');

// Bonuses
$router->get('/admin/hr/bonuses', 'App\\Http\\Controllers\\Admin\\HRController@bonuses');
$router->post('/admin/hr/bonuses/store', 'App\\Http\\Controllers\\Admin\\HRController@storeBonus');

// Salary Structure
$router->get('/admin/hr/salary-structure', 'App\\Http\\Controllers\\Admin\\HRController@salaryStructure');
$router->post('/admin/hr/salary-structure/store', 'App\\Http\\Controllers\\Admin\\HRController@storeSalaryStructure');
$router->get('/admin/hr/salary-structure/edit/{id}', 'App\\Http\\Controllers\\Admin\\HRController@editSalaryStructure');
$router->post('/admin/hr/salary-structure/update/{id}', 'App\\Http\\Controllers\\Admin\\HRController@updateSalaryStructure');

// Documents
$router->get('/admin/hr/documents', 'App\\Http\\Controllers\\Admin\\HRController@employeeDocuments');
$router->post('/admin/hr/documents/upload', 'App\\Http\\Controllers\\Admin\\HRController@uploadEmployeeDocument');

// Activities
$router->get('/admin/hr/activities', 'App\\Http\\Controllers\\Admin\\HRController@activities');

// Reports
$router->get('/admin/hr/report', 'App\\Http\\Controllers\\Admin\\HRController@employeeReport');

// Settings
$router->get('/admin/hr/settings', 'App\\Http\\Controllers\\Admin\\HRController@settings');

// ═══════════════════════════════════════════════════
// DOCUMENT MANAGEMENT SYSTEM
// ═══════════════════════════════════════════════════

// Core CRUD
$router->get('/admin/documents', 'App\\Http\\Controllers\\Admin\\DocumentController@index');
$router->get('/admin/documents/upload', 'App\\Http\\Controllers\\Admin\\DocumentController@upload');
$router->post('/admin/documents/store', 'App\\Http\\Controllers\\Admin\\DocumentController@store');
$router->get('/admin/documents/show/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@show');
$router->post('/admin/documents/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@delete');
$router->get('/admin/documents/download/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@download');

// Categories
$router->get('/admin/documents/categories', 'App\\Http\\Controllers\\Admin\\DocumentController@categories');
$router->post('/admin/documents/categories/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeCategory');
$router->post('/admin/documents/categories/update/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@updateCategory');
$router->post('/admin/documents/categories/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@deleteCategory');

// Types
$router->get('/admin/documents/types', 'App\\Http\\Controllers\\Admin\\DocumentController@types');
$router->post('/admin/documents/types/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeType');
$router->post('/admin/documents/types/update/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@updateType');
$router->post('/admin/documents/types/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@deleteType');

// Templates
$router->get('/admin/documents/templates', 'App\\Http\\Controllers\\Admin\\DocumentController@templates');
$router->post('/admin/documents/templates/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeTemplate');
$router->get('/admin/documents/templates/edit/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@editTemplate');
$router->post('/admin/documents/templates/update/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@updateTemplate');
$router->post('/admin/documents/templates/delete/{id}', 'App\\Http\\Controllers\\Admin\\DocumentController@deleteTemplate');

// Reviews
$router->get('/admin/documents/reviews', 'App\\Http\\Controllers\\Admin\\DocumentController@reviews');
$router->post('/admin/documents/reviews/store', 'App\\Http\\Controllers\\Admin\\DocumentController@storeReview');

// Classification & Linked Entities
$router->get('/admin/documents/classification', 'App\\Http\\Controllers\\Admin\\DocumentController@classification');
$router->get('/admin/documents/business', 'App\\Http\\Controllers\\Admin\\DocumentController@businessDocuments');
$router->get('/admin/documents/customer', 'App\\Http\\Controllers\\Admin\\DocumentController@customerDocuments');
$router->get('/admin/documents/user', 'App\\Http\\Controllers\\Admin\\DocumentController@userDocuments');
$router->get('/admin/documents/property', 'App\\Http\\Controllers\\Admin\\DocumentController@propertyDocuments');
$router->get('/admin/documents/generated', 'App\\Http\\Controllers\\Admin\\DocumentController@generatedDocuments');
$router->get('/admin/documents/ocr', 'App\\Http\\Controllers\\Admin\\DocumentController@ocrDocuments');

// Search
$router->get('/admin/documents/search', 'App\\Http\\Controllers\\Admin\\DocumentController@search');

// ═══════════════════════════════════════════════════
// ADMIN WORKFLOW CONTROLLER (25+ methods)
// ═══════════════════════════════════════════════════

// Dashboard
$router->get('/admin/workflows', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@dashboard');
$router->get('/admin/workflows/list', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@workflows');
$router->get('/admin/workflows/pending', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@pendingApprovals');
$router->any('/admin/workflows/create', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@createWorkflow');
$router->any('/admin/workflows/steps/{id}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@workflowSteps');
$router->post('/admin/workflows/action/{id}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@processWorkflowAction');

// Reports
$router->get('/admin/workflows/reports', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@reports');
$router->get('/admin/workflows/reports/sales', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@salesReport');
$router->get('/admin/workflows/reports/leads', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@leadsReport');
$router->get('/admin/workflows/reports/commission', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@commissionReport');
$router->post('/admin/workflows/reports/save', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@saveReport');

// Audit Trail
$router->get('/admin/workflows/audit', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@auditTrail');
$router->get('/admin/workflows/audit/entity/{entityType}/{entityId}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@entityHistory');
$router->get('/admin/workflows/audit/user/{userId}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@userActivity');

// Import / Export
$router->get('/admin/workflows/import-export', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@importExport');
$router->any('/admin/workflows/import-export/import', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@importData');
$router->get('/admin/workflows/import-export/export', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@exportData');
$router->get('/admin/workflows/import-export/template/{type}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@downloadTemplate');

// Backups
$router->get('/admin/workflows/backups', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@backups');
$router->post('/admin/workflows/backups/create', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@createBackup');
$router->get('/admin/workflows/backups/download/{filename}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@downloadBackup');

// Email Queue
$router->get('/admin/workflows/emails', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@emailQueue');
$router->post('/admin/workflows/emails/process', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@processEmailQueue');
$router->post('/admin/workflows/emails/retry', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@retryFailedEmails');

// API Documentation
$router->get('/admin/workflows/api-docs', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@apiDocs');
$router->get('/admin/workflows/api-docs/export/{format}', 'App\\Http\\Controllers\\Admin\\AdminWorkflowController@exportApiSpec');

// Task Management
$router->get('/admin/tasks', 'App\\Http\\Controllers\\Admin\\TaskController@index');
$router->get('/admin/tasks/create', 'App\\Http\\Controllers\\Admin\\TaskController@create');
$router->post('/admin/tasks/store', 'App\\Http\\Controllers\\Admin\\TaskController@store');
$router->get('/admin/tasks/show/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@show');
$router->get('/admin/tasks/edit/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@edit');
$router->post('/admin/tasks/update/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@update');
$router->post('/admin/tasks/destroy/{id}', 'App\\Http\\Controllers\\Admin\\TaskController@destroy');
$router->get('/admin/tasks/stats', 'App\\Http\\Controllers\\Admin\\TaskController@getStats');

// ═══════════════════════════════════════════════════
// SALARY & PAYMENT MANAGEMENT SYSTEM
// ═══════════════════════════════════════════════════

// Dashboard
$router->get('/admin/salary', 'App\\Http\\Controllers\\Admin\\SalaryController@index');
$router->get('/admin/salary/stats', 'App\\Http\\Controllers\\Admin\\SalaryController@stats');

// Salary Structures
$router->get('/admin/salary/structures', 'App\\Http\\Controllers\\Admin\\SalaryController@structures');
$router->get('/admin/salary/structures/edit/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@editStructure');
$router->post('/admin/salary/structures/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storeStructure');
$router->post('/admin/salary/structures/update/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@updateStructure');

// Salary Payments
$router->get('/admin/salary/payments', 'App\\Http\\Controllers\\Admin\\SalaryController@payments');
$router->get('/admin/salary/payments/create', 'App\\Http\\Controllers\\Admin\\SalaryController@createPayment');
$router->post('/admin/salary/payments/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storePayment');
$router->get('/admin/salary/payments/view/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@viewPayment');
$router->post('/admin/salary/payments/bulk', 'App\\Http\\Controllers\\Admin\\SalaryController@processBulk');

// Salary Payouts
$router->get('/admin/salary/payouts', 'App\\Http\\Controllers\\Admin\\SalaryController@payouts');
$router->post('/admin/salary/payouts/create', 'App\\Http\\Controllers\\Admin\\SalaryController@createPayout');
$router->post('/admin/salary/payouts/process/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@processPayout');

// Salary History
$router->get('/admin/salary/history', 'App\\Http\\Controllers\\Admin\\SalaryController@history');
$router->get('/admin/salary/history/employee/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@historyByEmployee');

// Salary Contracts
$router->get('/admin/salary/contracts', 'App\\Http\\Controllers\\Admin\\SalaryController@contracts');
$router->post('/admin/salary/contracts/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storeContract');
$router->get('/admin/salary/contracts/view/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@viewContract');
$router->post('/admin/salary/contracts/terminate/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@terminateContract');

// Salary Plans
$router->get('/admin/salary/plans', 'App\\Http\\Controllers\\Admin\\SalaryController@plans');
$router->post('/admin/salary/plans/store', 'App\\Http\\Controllers\\Admin\\SalaryController@storePlan');
$router->post('/admin/salary/plans/update/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@updatePlan');

// Salary Records
$router->get('/admin/salary/records', 'App\\Http\\Controllers\\Admin\\SalaryController@records');
$router->get('/admin/salary/records/{year}/{month}', 'App\\Http\\Controllers\\Admin\\SalaryController@recordByMonth');

// Salary Tracker
$router->get('/admin/salary/tracker', 'App\\Http\\Controllers\\Admin\\SalaryController@tracker');
$router->post('/admin/salary/tracker/update/{id}', 'App\\Http\\Controllers\\Admin\\SalaryController@updateTracker');

// Reports
$router->get('/admin/salary/report', 'App\\Http\\Controllers\\Admin\\SalaryController@report');
$router->get('/admin/salary/export-csv', 'App\\Http\\Controllers\\Admin\\SalaryController@exportCSV');

// Payroll Integration
$router->get('/admin/salary/payroll-integration', 'App\\Http\\Controllers\\Admin\\SalaryController@payrollIntegration');