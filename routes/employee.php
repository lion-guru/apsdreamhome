<?php
/**
 * Employee portal routes (extracted from web.php for maintainability)
 * S354 - Route file splitting
 * Loaded from public/index.php alongside web.php, land.php, gap_builds.php, ai.php
 */
// Employee Dashboard
$router->get('/employee/dashboard', 'Employee\\EmployeeController@dashboard');
$router->get('/employee/dashboard-overview', 'Employee\\EmployeeController@dashboardOverview');

// Employee Self-Service (HR features)
$router->get('/employee/self-service', 'Employee\\EmployeeSelfServiceController@index');
$router->get('/employee/self-service/training', 'Employee\\EmployeeSelfServiceController@training');
$router->get('/employee/self-service/org-chart', 'Employee\\EmployeeSelfServiceController@orgChart');
$router->get('/employee/self-service/org-chart/department', 'Employee\\EmployeeSelfServiceController@orgChartDepartment');
$router->get('/employee/self-service/surveys', 'Employee\\EmployeeSelfServiceController@surveys');
$router->get('/employee/self-service/surveys/{id}/take', 'Employee\\EmployeeSelfServiceController@surveyTake');
$router->post('/employee/self-service/surveys/{id}/submit', 'Employee\\EmployeeSelfServiceController@surveySubmit');
$router->get('/employee/self-service/surveys/{id}/results', 'Employee\\EmployeeSelfServiceController@surveyResults');
$router->get('/employee/self-service/advances', 'Employee\\EmployeeSelfServiceController@advances');
$router->get('/employee/self-service/advances/apply', 'Employee\\EmployeeSelfServiceController@advanceApply');
$router->post('/employee/self-service/advances/apply', 'Employee\\EmployeeSelfServiceController@advanceStore');
$router->get('/employee/self-service/advances/{id}', 'Employee\\EmployeeSelfServiceController@advanceDetail');
$router->get('/employee/self-service/encashment', 'Employee\\EmployeeSelfServiceController@encashment');
$router->get('/employee/self-service/encashment/apply', 'Employee\\EmployeeSelfServiceController@encashmentApply');
$router->post('/employee/self-service/encashment/apply', 'Employee\\EmployeeSelfServiceController@encashmentStore');
$router->get('/employee/self-service/overtime', 'Employee\\EmployeeSelfServiceController@overtime');
$router->get('/employee/self-service/overtime/apply', 'Employee\\EmployeeSelfServiceController@overtimeApply');
$router->post('/employee/self-service/overtime/apply', 'Employee\\EmployeeSelfServiceController@overtimeStore');
$router->get('/employee/self-service/overtime/{id}', 'Employee\\EmployeeSelfServiceController@overtimeDetail');
$router->get('/employee/self-service/assets', 'Employee\\EmployeeSelfServiceController@assets');
$router->post('/employee/self-service/assets/report-issue', 'Employee\\EmployeeSelfServiceController@reportAssetIssue');
$router->get('/employee/self-service/announcements', 'Employee\\EmployeeSelfServiceController@announcements');
$router->get('/employee/self-service/announcements/{id}/read', 'Employee\\EmployeeSelfServiceController@markAnnouncementRead');
$router->get('/employee/self-service/shift-swaps', 'Employee\\EmployeeSelfServiceController@shiftSwaps');
$router->get('/employee/self-service/shift-swaps/request', 'Employee\\EmployeeSelfServiceController@shiftSwapRequest');
$router->post('/employee/self-service/shift-swaps/request', 'Employee\\EmployeeSelfServiceController@shiftSwapStore');
$router->get('/employee/self-service/shift-swaps/{id}/respond', 'Employee\\EmployeeSelfServiceController@shiftSwapRespond');
$router->post('/employee/self-service/shift-swaps/{id}/respond', 'Employee\\EmployeeSelfServiceController@shiftSwapRespondStore');
$router->get('/employee/self-service/helpdesk', 'Employee\\EmployeeSelfServiceController@helpdesk');
$router->get('/employee/self-service/helpdesk/create', 'Employee\\EmployeeSelfServiceController@helpdeskCreate');
$router->post('/employee/self-service/helpdesk/create', 'Employee\\EmployeeSelfServiceController@helpdeskStore');
$router->get('/employee/self-service/helpdesk/{id}', 'Employee\\EmployeeSelfServiceController@helpdeskDetail');
$router->get('/employee/self-service/grievances', 'Employee\\EmployeeSelfServiceController@grievances');
$router->get('/employee/self-service/grievances/create', 'Employee\\EmployeeSelfServiceController@grievanceCreate');
$router->post('/employee/self-service/grievances/create', 'Employee\\EmployeeSelfServiceController@grievanceStore');
$router->get('/employee/self-service/grievances/{id}', 'Employee\\EmployeeSelfServiceController@grievanceDetail');
$router->post('/employee/self-service/grievances/{id}/comment', 'Employee\\EmployeeSelfServiceController@grievanceComment');
$router->post('/employee/self-service/grievances/{id}/close', 'Employee\\EmployeeSelfServiceController@grievanceClose');
$router->get('/employee/self-service/tax-calculator', 'Employee\\EmployeeSelfServiceController@taxCalculator');
$router->get('/employee/self-service/tax-regime', 'Employee\\EmployeeSelfServiceController@taxRegime');
$router->post('/employee/self-service/tax-regime', 'Employee\\EmployeeSelfServiceController@taxRegimeStore');
$router->get('/employee/self-service/certifications', 'Employee\\EmployeeSelfServiceController@certifications');
$router->get('/employee/self-service/encashment', 'Employee\\EmployeeSelfServiceController@encashment');
$router->get('/employee/self-service/encashment/apply', 'Employee\\EmployeeSelfServiceController@encashmentApply');
$router->post('/employee/self-service/encashment/apply', 'Employee\\EmployeeSelfServiceController@encashmentStore');
$router->get('/employee/self-service/leave-calendar', 'Employee\\EmployeeSelfServiceController@leaveCalendar');

// Employee Telecalling
$router->get('/employee/telecalling-dashboard', 'Employee\\TelecallingController@dashboard');
$router->post('/employee/telecalling/log-call', 'Employee\\TelecallingController@logCall');
$router->get('/employee/telecalling/desk', 'Employee\\TelecallingController@callingDesk');
$router->post('/employee/telecalling/book-site-visit', 'Employee\\TelecallingController@bookSiteVisit');
$router->get('/employee/telecalling/lead/{id}', 'Employee\\TelecallingController@getLeadDetails');
$router->get('/employee/telecalling/script/{id}', 'Employee\\TelecallingController@getRecommendedScript');
$router->get('/employee/telecalling/followups', 'Employee\\TelecallingController@getTodayFollowUps');
$router->post('/employee/telecalling/complete-followup', 'Employee\\TelecallingController@completeFollowUp');

// Employee Manual Calling Desk (Jio Fiber Free)
$router->get('/employee/manual-calling', 'Employee\\ManualCallingDeskController@index');
$router->post('/employee/manual-calling/log-call', 'Employee\\ManualCallingDeskController@logCall');
$router->post('/employee/manual-calling/send-sms-template', 'Employee\\ManualCallingDeskController@sendSmsTemplate');

// Employee Inquiries
$router->get('/employee/inquiries', 'Employee\\EmployeeController@inquiries');
$router->get('/employee/inquiries/{id}', 'Employee\\EmployeeController@inquiryDetail');
$router->post('/employee/inquiries/{id}/status', 'Employee\\EmployeeController@updateInquiryStatus');
$router->post('/employee/inquiries/{id}/followup', 'Employee\\EmployeeController@addInquiryFollowup');

// Employee Dashboard & other
$router->get('/employee/dashboard', 'Employee\\EmployeeController@dashboard');
$router->get('/employee/attendance', 'Employee\\EmployeeController@attendance');
$router->get('/employee/leaves', 'Employee\\EmployeeController@leaves');
$router->get('/employee/payslips', 'Employee\\EmployeeController@payslips');
$router->get('/employee/documents', 'Employee\\EmployeeController@documents');
$router->get('/employee/profile', 'Employee\\EmployeeController@profile');
$router->get('/employee/notifications', 'Employee\\EmployeeController@notifications');
$router->get('/employee/change-password', 'Employee\\EmployeeController@changePassword');
$router->get('/employee/checkin', 'Employee\\EmployeeController@checkin');
$router->get('/employee/checkout', 'Employee\\EmployeeController@checkout');
$router->get('/employee/activities', 'Employee\\EmployeeController@activities');

// Employee HR Dashboard
$router->get('/employee/hr-dashboard', 'Employee\\HRManagerController@dashboard');
$router->get('/employee/legal-dashboard', 'Employee\\LegalAdvisorController@dashboard');
$router->get('/employee/ca-dashboard', 'Employee\\CAController@dashboard');
$router->get('/employee/ops-dashboard', 'Employee\\EmployeeController@opsDashboard');
$router->get('/employee/land-dashboard', 'Employee\\LandManagerController@dashboard');
$router->get('/employee/sales-dashboard', 'Employee\\EmployeeController@salesDashboard');
$router->get('/employee/marketing-dashboard', 'Employee\\EmployeeController@marketingDashboard');
$router->get('/employee/reporting', 'Employee\\EmployeeController@reporting');
$router->get('/employee/self-service', 'Employee\\EmployeeSelfServiceController@index');

// Repointed menu items (S343/S347)
$router->get('/employee/reports', 'Employee\\EmployeeController@reporting');
$router->get('/employee/deals', 'Employee\\EmployeeController@salesDashboard');
$router->get('/employee/employees', 'Employee\\HRManagerController@dashboard');
$router->get('/employee/infrastructure', 'Employee\\EmployeeController@opsDashboard');
$router->get('/employee/construction-dashboard', 'Employee\\CAController@dashboard');
$router->get('/employee/projects', 'Employee\\LandManagerController@dashboard');
$router->get('/employee/quality', 'Employee\\EmployeeSelfServiceController@index');
$router->get('/employee/campaigns', 'Employee\\EmployeeController@marketingDashboard');
$router->get('/employee/vendors', 'Employee\\EmployeeController@opsDashboard');
$router->get('/employee/cs-dashboard', 'Employee\\EmployeeController@dashboardOverview');
$router->get('/employee/tax', 'Employee\\EmployeeSelfServiceController@taxCalculator');
$router->get('/employee/recruitment', 'Employee\\HRManagerController@dashboard');
$router->get('/employee/compliance', 'Employee\\LegalAdvisorController@dashboard');
$router->get('/employee/surveys', 'Employee\\EmployeeSelfServiceController@surveys');
$router->get('/employee/complaints', 'Employee\\LegalAdvisorController@dashboard');